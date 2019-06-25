<?php

namespace Plasticode\Handlers;

use Plasticode\Contained;
use Plasticode\Core\Core;

class ErrorHandler extends Contained
{
    private $debug;
    
    public function __construct($container, $debug = false)
    {
        parent::__construct($container);
        
        $this->debug = $debug;
    }
    
    public function __invoke($request, $response, $exception)
    {
        if ($this->debug && !Core::isJsonRequest($request)) {
            throw $exception;
        }
        
        return Core::error($this->container, $response, $exception);
    }
}
