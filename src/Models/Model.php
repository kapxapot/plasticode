<?php

namespace Plasticode\Models;

use Plasticode\Contained;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Traits\LazyCache;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class Model implements \ArrayAccess, \JsonSerializable, ArrayableInterface
{
    use LazyCache;

    /**
     * DI container wrapper (!)
     *
     * @var Contained
     */
    protected static $container;

    /**
     * Data array or \ORM object
     *
     * @var array|\ORM
     */
    protected $obj;

    /** @var boolean */
    private static $initialized = false;

    public static function init(ContainerInterface $container) : void
    {
        if (!self::$initialized) {
            // hack for getSettings()
            self::$container = new Contained($container);

            self::$initialized = true;
        }
    }

    /**
     * Creates instance.
     *
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

    protected static function getSettings(string $path)
    {
        return self::$container->getSettings($path);
    }
    
    private function checkPropertyExists(string $property) : void
    {
        if (array_key_exists($property, $this->obj)) {
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
            $this->checkPropertyExists($snakeCase);
            return $this->{$camelCase}();
        }
        
        return $this->obj[$snakeCase] ?? null;
    }
    
    public function __set(string $property, $value)
    {
        $camelCase = Strings::toCamelCase($property);
        $snakeCase = Strings::toSnakeCase($property);
        
        if (method_exists($this, $camelCase)) {
            $this->checkPropertyExists($snakeCase);
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
