<?php

namespace Plasticode\Handlers;

use Plasticode\Contained;
use Plasticode\Core\Core;
use Plasticode\Util\Strings;

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
		$contentType = $request->getHeaderLine('Accept');

    	if ($this->debug && Strings::startsWith($contentType, 'text/html')) {
    	    throw $exception;
    	}
    	
    	return Core::error($this->container, $response, $exception, $this->debug);
	}
}
