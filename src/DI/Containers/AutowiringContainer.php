<?php

namespace Plasticode\DI\Containers;

use Exception;
use Plasticode\DI\Autowirer;
use Plasticode\DI\Transformations\CallableResolver;
use Plasticode\Exceptions\DI\ContainerException;
use Plasticode\Exceptions\DI\NotFoundException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AutowiringContainer extends AggregatingContainer
{
    /** @var array<string, object> */
    private array $resolved;

    private Autowirer $autowirer;

    /** @var callable[] */
    private array $transformations;

    public function __construct(?array $map = null)
    {
        parent::__construct($map);

        $this->resolved = [
            ContainerInterface::class => $this
        ];

        $this->autowirer = new Autowirer();

        $this->transformations = [
            new CallableResolver()
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
            return $this->getResolved($id);
        }

        // [defined] => ...
        if (parent::has($id)) {
            $value = parent::get($id);

            // - [defined] => [object] -> save to resolved
            if (!is_string($value)) {
                return $this->setResolved($id, $value);
            }

            // - [defined] => [string] -> get(value), save to resolved
            return $this->setResolved($id, $this->get($value));
        }

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
        $object = $this->transform($object);

        $this->resolved[$id] = $object;

        return $object;
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
                'Error while transforming an object of the class' .
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
