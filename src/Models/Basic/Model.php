<?php

namespace Plasticode\Models\Basic;

use ArrayAccess;
use JsonSerializable;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Traits\Convert\ToBit;
use Plasticode\Traits\Convert\ToBool;
use Plasticode\Traits\Convert\ToIso;
use Plasticode\Traits\PropertyAccess;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

class Model implements ArrayableInterface, ArrayAccess, JsonSerializable
{
    use PropertyAccess;
    use ToBit;
    use ToBool;
    use ToIso;

    /**
     * Data array.
     */
    protected array $data;

    public function __construct(?array $data = null)
    {
        $this->data = $data ?? [];
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function failIfPropertyExists(string $property) : void
    {
        if (self::propertyExists($this->data, $property)) {
            $className = static::class;

            throw new InvalidConfigurationException(
                'Property conflict in model: ' .
                'method defined over existing property. ' .
                'Class: ' . $className . ', property: ' . $property
            );
        }
    }

    public function __get(string $property)
    {
        $camelCase = Strings::toCamelCase($property);
        $snakeCase = Strings::toSnakeCase($property);

        if (method_exists($this, $camelCase)) {
            $this->failIfPropertyExists($snakeCase);
            return $this->{$camelCase}();
        }

        return $this->data[$snakeCase] ?? null;
    }

    public function __set(string $property, $value)
    {
        $camelCase = Strings::toCamelCase($property);
        $snakeCase = Strings::toSnakeCase($property);

        if (method_exists($this, $camelCase)) {
            $this->failIfPropertyExists($snakeCase);
            $this->{$camelCase}($value);
        } else {
            $this->data[$snakeCase] = $value;
        }
    }

    public function __isset(string $property)
    {
        $camelCase = Strings::toCamelCase($property);

        if (method_exists($this, $camelCase)) {
            return $this->{$camelCase}() !== null;
        }

        $snakeCase = Strings::toSnakeCase($property);
        return isset($this->data[$snakeCase]);
    }

    public function __unset(string $property)
    {
        $camelCase = Strings::toCamelCase($property);

        if (method_exists($this, $camelCase)) {
            return $this->{$camelCase}(null);
        }

        $snakeCase = Strings::toSnakeCase($property);
        unset($this->data[$snakeCase]);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return static::class;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray() : array
    {
        return $this->data;
    }

    public function offsetSet($offset, $value)
    {
        Assert::notNull($offset);

        $this->{$offset} = $value;
    }

    public function offsetExists($offset) : bool
    {
        return isset($this->{$offset});
    }

    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return isset($this->{$offset})
            ? $this->{$offset}
            : null;
    }
}
