<?php

namespace Plasticode\DI\Containers;

use Plasticode\DI\Autowirer;
use Plasticode\DI\Interfaces\ContainerFactoryInterface;
use Plasticode\Exceptions\InvalidConfigurationException;

class AutowiredContainer extends ArrayContainer
{
    private Autowirer $autowirer;

    public function __construct(?array $map = null)
    {
        parent::__construct($map);

        $this->autowirer = new Autowirer($this);
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            // no mapping, trying autowire
            return $this->autowire($id);
        }

        $value = parent::get($id);

        // if value is string, it is an alias
        // (interface => interface, interface => class)
        return is_string($value)
            ? $this->get($value)
            : $value;
    }

    /**
     * @return mixed
     * 
     * @throws InvalidConfigurationException
     */
    protected function autowire(string $className)
    {
        $object = $this->autowirer->autowire($className);

        if ($object instanceof ContainerFactoryInterface) {
            $object = ($object)($this);
        }

        $this[$className] = $object;

        return $object;
    }
}
