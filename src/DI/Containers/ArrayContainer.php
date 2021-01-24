<?php

namespace Plasticode\DI\Containers;

use Exception;
use InvalidArgumentException;
use Plasticode\DI\Interfaces\ArrayContainerInterface;
use Webmozart\Assert\Assert;

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

    // ContainerInterface

    /**
     * @param string $id
     * @return mixed
     * 
     * @throws Exception
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new Exception('Mapping for ' . $id . ' is not defined.');
        }

        return $this->map[$id];
    }

    /**
     * @param string $id
     * @return bool
     * 
     * @throws InvalidArgumentException
     */
    public function has($id)
    {
        Assert::notNull($id);

        return isset($this->map[$id]);
    }

    // ArrayAccess

    public function offsetSet($offset, $value)
    {
        Assert::notNull($offset);
        Assert::notNull($value);

        $this->map[$offset] = $value;
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
