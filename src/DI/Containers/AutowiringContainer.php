<?php

namespace Plasticode\DI\Containers;

use Exception;
use Plasticode\DI\Autowirer;
use Plasticode\Exceptions\DI\ContainerException;
use Plasticode\Exceptions\DI\NotFoundException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AutowiringContainer extends ArrayContainer
{
    private Autowirer $autowirer;

    /** @var callable[] */
    private array $transformations;

    public function __construct(?array $map = null)
    {
        parent::__construct($map);

        $this->autowirer = new Autowirer();
        $this->transformations = [];
    }

    /**
     * @param callable $transformation fn (ContainerInterface, object): object
     * 
     * @return $this
     */
    public function withTransformation(callable $transformation): self
    {
        $this->transformations[] = $transformation;

        return $this;
    }

    public function get($id)
    {
        if (!parent::has($id)) {
            // not resolved yet, trying autowire
            // this works only for classes (!)
            $object = $this->autowire($id);
            $this->set($id, $object);

            return $object;
        }

        // already resolved or an alias/factory mapping...
        $value = parent::get($id);

        if (!is_string($value)) {
            // already resolved, just return
            return $value;
        }

        // if value is a string, it is an alias or a factory:
        // 
        // - interface => interface (alias)
        // - interface => class
        // - interface => factory
        // - class => interface (this is weird, but viable)
        // - class => class (alias)
        // - class => factory

        if (parent::has($value)) {
            // already resolved, just return
            return $this->get($value);
        }

        // not resolved yet, trying autowire
        // this works only for classes (!)
        // also, performs transformations (e.g., from factory to instance)
        $object = $this->autowire($value);
        $object = $this->transform($object);
        $this->set($id, $object);

        return $object;
    }

    public function has($id)
    {
        return parent::has($id) || $this->autowirer->canAutowire($this, $id);
    }

    /**
     * @throws ContainerExceptionInterface
     */
    public function transform(object $value): object
    {
        try {
            foreach ($this->transformations as $transformation) {
                $value = ($transformation)($this, $value);
            }

            return $value;
        } catch (Exception $ex) {
            $message =
                'Error while transforming an object of the class' .
                get_class($value);

            throw new ContainerException($message, 0, $ex);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function autowire(string $className)
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
