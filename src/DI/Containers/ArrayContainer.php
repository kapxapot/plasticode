<?php

namespace Plasticode\DI\Containers;

use Plasticode\DI\Interfaces\ArrayContainerInterface;
use Plasticode\Exceptions\DI\NotFoundException;

class ArrayContainer implements ArrayContainerInterface
{
    /** @var array<string, mixed> */
    private array $map;

    /**
     * @param array<string, mixed>|null $map
     */
    public function __construct(?array $map = null)
    {
        $this->map = $map ?? [];
    }

    /**
     * @param mixed $value
     */
    public function set(string $id, $value): void
    {
        $this->map[$id] = $value;
    }

    // ContainerInterface

    /**
     * @param string $id
     * @return mixed
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new NotFoundException('Mapping for ' . $id . ' is not defined.');
        }

        return $this->map[$id];
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id)
    {
        return isset($this->map[$id]);
    }

    // ArrayAccess

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetUnset($offset)
    {
        unset($this->map[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }
}
