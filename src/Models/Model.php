<?php

namespace Plasticode\Models;

use Plasticode\Contained;
use Plasticode\Core\Cache;
use Plasticode\Util\Strings;

abstract class Model implements \ArrayAccess
{
    protected static $container;
    
    protected static $db;
    protected static $auth;
    protected static $linker;
    protected static $cases;
    protected static $parser;
    
    protected static $userRepository;
    protected static $roleRepository;
    protected static $menuItemRepository;

    protected $obj;
    protected $objCache;
    protected static $staticCache;

    public static function init($container)
    {
        self::$container = new Contained($container);
        
        self::$db = self::$container->db;
        self::$auth = self::$container->auth;
        self::$linker = self::$container->linker;
        self::$cases = self::$container->cases;
        self::$parser = self::$container->parser;
        
        self::$userRepository = self::$container->userRepository;
        self::$roleRepository = self::$container->roleRepository;
        self::$menuItemRepository = self::$container->menuItemRepository;
        
        self::$staticCache = new Cache();
    }

    public function __construct($obj = null)
    {
        $this->obj = $obj ?? [];
        $this->objCache = new Cache();
    }

    protected static function getSettings($path)
    {
        return self::$container->getSettings($path);
    }

	// repositories
	
	protected static function getUser($id)
	{
	    return self::$userRepository->get($id);
	}
	
	protected static function getRole($id)
	{
	    return self::$roleRepository->get($id);
	}

	// lazy
	
    private static function getLazyFuncName()
    {
        list($one, $two, $caller) = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        return $caller['function'];
    }
	
	protected function lazy(\Closure $loader, $name = null, bool $ignoreCache = false)
	{
	    $name = $name ?? self::getLazyFuncName();
	    
	    if ($ignoreCache === true || !$this->objCache->exists($name)) {
	        $this->objCache->set($name, $loader());
	    }
	    
	    return $this->objCache->get($name);
	}
	
	protected function resetLazy($name)
	{
	    $this->objCache->delete($name);
	}
	
	protected static function staticLazy(\Closure $loader, $name = null, bool $ignoreCache = false)
	{
	    $name = $name ?? self::getLazyFuncName();
	    
	    if ($ignoreCache === true || !self::$staticCache->exists($name)) {
	        self::$staticCache->set($name, $loader());
	    }
	    
	    return self::$staticCache->get($name);
	}
	
	protected static function resetStaticLazy($name)
	{
	    self::$staticCache->delete($name);
	}

    // magic
    
    private function checkPropertyExists($property)
    {
	    if (array_key_exists($property, $this->obj)) {
	        $className = static::class;
	        throw new \Exception("Property conflict in model: method defined over existing property. Class: {$className}, Property: {$property}.");
	    }
    }

	public function __get($property)
	{
	    $camelCase = Strings::toCamelCase($property);
        $snakeCase = Strings::toSnakeCase($property);
	    
		if (method_exists($this, $camelCase)) {
		    $this->checkPropertyExists($snakeCase);
		    return $this->{$camelCase}();
		}
        
		return $this->obj[$snakeCase];
	}
	
	public function __set($property, $value)
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
	
	public function __isset($property)
	{
	    $camelCase = Strings::toCamelCase($property);
	    
		if (method_exists($this, $camelCase)) {
		    return $this->{$camelCase}() !== null;
		}
		
        $snakeCase = Strings::toSnakeCase($property);
		return isset($this->obj[$snakeCase]);
	}
	
	public function __unset($property)
	{
	    $camelCase = Strings::toCamelCase($property);
	    
		if (method_exists($this, $camelCase)) {
		    return $this->{$camelCase}(null);
		}
		
        $snakeCase = Strings::toSnakeCase($property);
		unset($this->obj[$snakeCase]);
	}

	public function __toString()
	{
	    return $this->toString();
	}
	
	public function toString()
	{
	    return static::class;
	}
	
	// array access
	
    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            throw new \InvalidArgumentException('$offset cannot be null.');
        } else {
            $this->{$offset} = $value;
        }
    }

    public function offsetExists($offset) {
        return isset($this->{$offset});
    }

    public function offsetUnset($offset) {
        unset($this->{$offset});
    }

    public function offsetGet($offset) {
        return isset($this->{$offset})
            ? $this->{$offset}
            : null;
    }
}
