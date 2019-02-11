<?php

namespace Plasticode;

class StaticProxy
{
    protected $modelClass;
    
    public function __construct($modelClass)
    {
        $this->modelClass = $modelClass;
    }
    
    protected function getModelClass()
    {
        $class = $this->modelClass ?? null;
        
        if (strlen($class) == 0) {
            throw new \Exception('Model class not set for proxy ' . static::class);
        }
        
        return $class;
    }
    
    public function __call($method, $args)
    {
        $class = self::getModelClass();
        
        if (!method_exists($class, $method)) {
            throw new \Exception("Class {$class} doesn't have method {$method}.");
        }
        
        return $class::$method(...$args);
    }
}
