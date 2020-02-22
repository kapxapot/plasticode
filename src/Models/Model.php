<?php

namespace Plasticode\Models;

use Plasticode\Auth\Auth;
use Plasticode\Contained;
use Plasticode\Core\Cache;
use Plasticode\Core\Linker;
use Plasticode\Data\Db;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Models\Role;
use Plasticode\Models\User;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Util\Cases;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class Model implements \ArrayAccess, \JsonSerializable, ArrayableInterface
{
    /**
     * DI container wrapper (!)
     *
     * @var Contained
     */
    protected static $container;
    
    /**
     * Db layer
     *
     * @var Db
     */
    protected static $db;

    /** @var Auth */
    protected static $auth;

    /** @var Linker */
    protected static $linker;

    /** @var Cases */
    protected static $cases;

    /** @var CompositeParser */
    protected static $parser;
    
    protected static $userRepository;
    protected static $roleRepository;
    protected static $menuItemRepository;

    /**
     * Data array or \ORM object
     *
     * @var array|\ORM
     */
    protected $obj;

    /**
     * Instance cache
     *
     * @var Cache
     */
    protected $objCache;

    /**
     * Static cache
     *
     * @var Cache
     */
    private static $staticCache;

    /** @var boolean */
    private static $initialized = false;

    public static function init(ContainerInterface $container) : void
    {
        if (!self::$initialized) {
            // hack for getSettings()
            self::$container = new Contained($container);

            self::$db = self::$container->db;
            
            self::$userRepository = self::$container->userRepository;
            self::$roleRepository = self::$container->roleRepository;
            self::$menuItemRepository = self::$container->menuItemRepository;
            
            self::$auth = self::$container->auth;
            self::$linker = self::$container->linker;
            self::$cases = self::$container->cases;
            self::$parser = self::$container->parser;

            self::$initialized = true;
        }
    }

    /**
     * Creates instance.
     *
     * @param array|\ORM $obj
     */
    public function __construct($obj = null)
    {
        $this->obj = $obj ?? [];
        $this->objCache = new Cache();
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
    
    protected static function getUser($id) : ?User
    {
        return self::$userRepository->get($id);
    }
    
    protected static function getRole($id) : ?Role
    {
        return self::$roleRepository->get($id);
    }
    
    protected static function getCurrentUser() : ?User
    {
        return self::$auth->getUser();
    }
    
    private static function getLazyFuncName() : string
    {
        list(, , $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        return $caller['function'];
    }
    
    protected function lazy(\Closure $loader, string $name = null, bool $ignoreCache = false)
    {
        $name = $name ?? self::getLazyFuncName();
        
        return $this->objCache->getCached($name, $loader, $ignoreCache);
    }
    
    protected function resetLazy(string $name) : void
    {
        $this->objCache->delete($name);
    }
    
    protected static function staticLazy(\Closure $loader, string $name = null, bool $ignoreCache = false)
    {
        $cache = self::getStaticCache();
        $name = $name ?? self::getLazyFuncName();
        
        return $cache->getCached($name, $loader, $ignoreCache);
    }
    
    protected static function resetStaticLazy(string $name) : void
    {
        $cache = self::getStaticCache();
        $cache->delete($name);
    }

    private static function getStaticCache(): Cache
    {
        if (is_null(self::$staticCache)) {
            self::$staticCache = new Cache();
        }

        return self::$staticCache;
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
        
        return $this->obj[$snakeCase];
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
