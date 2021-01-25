<?php

namespace Plasticode\DI\Containers;

use Plasticode\DI\Autowirer;
use Plasticode\Exceptions\DI\ContainerException;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerExceptionInterface;

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
            // no mapping, trying autowire
            $object = $this->autowire($id);
            $this->set($id, $object);

            return $object;
        }

        $value = parent::get($id);

        // if value is string, it is an alias
        // (interface => interface, interface => class, interface => factory, class => factory)
        if (is_string($value)) {
            $object = $this->autowire($value);
            $object = $this->transform($object);
            $this->set($id, $object);

            return $object;
        }

        return $value;
    }

    public function has($id)
    {
        return parent::has($id) || $this->autowirer->canAutowire($this, $id);
    }

    public function transform(object $value): object
    {
        foreach ($this->transformations as $transformation) {
            $value = ($transformation)($this, $value);
        }

        return $value;
    }

    /**
     * @throws ContainerExceptionInterface
     */
    protected function autowire(string $className)
    {
        try {
            return $this->autowirer->autowire($this, $className);
        } catch (InvalidConfigurationException $ex) {
            throw new ContainerException('Failed to autowire ' . $className, 0, $ex);
        }
    }
}
