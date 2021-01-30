<?php

namespace Plasticode\DI\Containers;

use Exception;
use Plasticode\DI\Autowirer;
use Plasticode\DI\Transformations\CallableResolver;
use Plasticode\Exceptions\DI\ContainerException;
use Plasticode\Exceptions\DI\NotFoundException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Traits\LoggerAwareTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AutowiringContainer extends AggregatingContainer
{
    use LoggerAwareTrait;

    /** @var array<string, object> */
    private array $resolved;

    private Autowirer $autowirer;

    /** @var callable[] */
    private array $transformations;

    public function __construct(
        Autowirer $autowirer,
        ?array $map = null
    )
    {
        parent::__construct($map);

        $this->resolved = [
            ContainerInterface::class => $this
        ];

        $this->autowirer = $autowirer;

        $this->transformations = [
            new CallableResolver($this->autowirer)
        ];
    }

    /**
     * @param callable $transformation fn (ContainerInterface, object): object
     * 
     * @return static
     */
    public function withTransformation(callable $transformation): self
    {
        $this->transformations[] = $transformation;

        return $this;
    }

    /**
     * Can get:
     * 
     * - [this] -> return this
     * - [resolved] -> return resolved
     * - [undefined] -> try autowire, save to resolved
     * - [defined] => [object] -> save to resolved
     * - [defined] => [string] -> get(value), save to resolved
     */
    public function get($id)
    {
        // [resolved] -> return resolved
        if ($this->isResolved($id)) {
            $this->log($id . ' resolved');

            return $this->getResolved($id);
        }

        $this->log($id . ' not resolved');

        // [defined] => ...
        if (parent::has($id)) {
            $this->log($id . ' parent has');

            $value = parent::get($id);

            // - [defined] => [object] -> save to resolved
            if (!is_string($value)) {
                $this->log($id . ' not string');

                return $this->setResolved($id, $value);
            }

            $this->log($id . ' string');

            // - [defined] => [string] -> get(value), save to resolved
            return $this->setResolved($id, $this->get($value));
        }

        $this->log($id . ' parent doesn\'t have');

        // [undefined] -> try autowire, save to resolved
        return $this->setResolved($id, $this->autowire($id));
    }

    public function has($id)
    {
        return parent::has($id)
            || $this->isResolved($id)
            || $this->autowirer->canAutowire($this, $id);
    }

    protected function isResolved(string $id): bool
    {
        return array_key_exists($id, $this->resolved);
    }

    protected function getResolved(string $id): object
    {
        return $this->resolved[$id];
    }

    protected function setResolved(string $id, object $object): object
    {
        $this->log($id . ' trying to transform ' . get_class($object));

        $newObject = $this->transform($object);

        $this->log($id . ' transformed ' . get_class($object) . ' to ' . get_class($newObject));

        $this->resolved[$id] = $newObject;

        return $newObject;
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function transform(object $object): object
    {
        try {
            foreach ($this->transformations as $transformation) {
                $object = ($transformation)($this, $object);
            }

            return $object;
        } catch (Exception $ex) {
            $message =
                'Error while transforming an object of the class ' .
                get_class($object);

            throw new ContainerException($message, 0, $ex);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function autowire(string $className): object
    {
        try {
            return $this->autowirer->autowire($this, $className);
        }
        catch (InvalidConfigurationException $ex) {
            throw new NotFoundException('Failed to autowire ' . $className, 0, $ex);
        }
        catch (Exception $ex) {
            throw new ContainerException('Error while autowiring ' . $className, 0, $ex);
        }
    }
}
