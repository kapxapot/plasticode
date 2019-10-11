<?php

namespace Plasticode;

use Plasticode\Exceptions\InvalidArgumentException;
use Plasticode\Exceptions\InvalidConfigurationException;

class StaticProxy
{
    protected $modelClass;
    
    public function __construct(string $modelClass)
    {
        if (strlen($modelClass) == 0) {
            throw new InvalidArgumentException('Model class not set.');
        }

        $this->modelClass = $modelClass;
    }
    
    public function __call(string $method, array $args)
    {
        $class = $this->modelClass;
        
        if (!method_exists($class, $method)) {
            throw new InvalidConfigurationException(
                'Class ' . $class . ' doesn\'t have method ' . $method . '.'
            );
        }
        
        return $class::$method(...$args);
    }
}
