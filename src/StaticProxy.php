<?php

namespace Plasticode;

class StaticProxy
{
    protected $modelClass;
    
    public function __construct($modelClass)
    {
        if (strlen($modelClass) == 0) {
            throw new \Exception('Model class not set.');
        }

        $this->modelClass = $modelClass;
    }
    
    public function __call($method, $args)
    {
        $class = $this->modelClass;
        
        if (!method_exists($class, $method)) {
            throw new \Exception('Class ' . $class . ' doesn\'t have method ' . $method . '.');
        }
        
        return $class::$method(...$args);
    }
}
