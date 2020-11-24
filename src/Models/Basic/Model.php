<?php

namespace Plasticode\Models\Basic;

use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Traits\PropertyAccess;
use Plasticode\Util\Convert;
use Plasticode\Util\Date;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

class Model implements \ArrayAccess, \JsonSerializable, ArrayableInterface
{
    use PropertyAccess;

    /**
     * Data array or \ORM object.
     *
     * @var array|\ORM
     */
    protected $obj;

    /**
     * @param array|\ORM|null $obj
     */
    public function __construct($obj = null)
    {
        $this->obj = $obj ?? [];
    }

    /**
     * Returns the underlying obj - array or \ORM.
     *
     * @return array|\ORM
     */
    public function getObj()
    {
        return $this->obj;
    }

    protected static function toBool(?int $value) : bool
    {
        return Convert::fromBit($value);
    }

    protected static function toBit(?bool $value) : int
    {
        return Convert::toBit($value);
    }

    /**
     * Null => null!
     */
    protected static function toIso(?string $date) : ?string
    {
        return $date
            ? Date::iso($date)
            : null;
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function failIfPropertyExists(string $property) : void
    {
        if (self::propertyExists($this->obj, $property)) {
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

        return $this->obj[$snakeCase] ?? null;
    }

    public function __set(string $property, $value)
    {
        $camelCase = Strings::toCamelCase($property);
        $snakeCase = Strings::toSnakeCase($property);

        if (method_exists($this, $camelCase)) {
            $this->failIfPropertyExists($snakeCase);
            $this->{$camelCase}($value);
        } else {
            $this->obj[$snakeCase] = $value;
        }
    }

    public function __isset(string $property)
    {
        $camelCase = Strings::toCamelCase($property);

        if (method_exists($this, $camelCase)) {
            return $this->{$camelCase}() !== null;
        }

        $snakeCase = Strings::toSnakeCase($property);
        return isset($this->obj[$snakeCase]);
    }

    public function __unset(string $property)
    {
        $camelCase = Strings::toCamelCase($property);

        if (method_exists($this, $camelCase)) {
            return $this->{$camelCase}(null);
        }

        $snakeCase = Strings::toSnakeCase($property);
        unset($this->obj[$snakeCase]);
    }

    public function __toString() : string
    {
        return $this->toString();
    }

    public function toString() : string
    {
        return static::class;
    }

    public function toArray() : array
    {
        return $this->obj instanceof \ORM
            ? $this->obj->asArray()
            : $this->obj;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
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
