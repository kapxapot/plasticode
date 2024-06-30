<?php

namespace Plasticode\Models\Generic;

use ArrayAccess;
use JsonSerializable;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Traits\Convert\ToBit;
use Plasticode\Traits\Convert\ToBool;
use Plasticode\Traits\Convert\ToIso;
use Plasticode\Traits\GetClass;
use Plasticode\Traits\PropertyAccess;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

class Model implements ArrayableInterface, ArrayAccess, JsonSerializable
{
    use GetClass;
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
    private function failIfPropertyExists(string $property): void
    {
        if (self::propertyExists($this->data, $property)) {
            throw new InvalidConfigurationException(
                'Property conflict in model: ' .
                'method defined over existing property. ' .
                'Class: ' . $this->getClass() . ', property: ' . $property
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

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return $this->getClass();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function offsetSet($offset, $value): void
    {
        Assert::notNull($offset);

        $this->{$offset} = $value;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetUnset($offset): void
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
