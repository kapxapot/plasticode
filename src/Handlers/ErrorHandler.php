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
		// to do
		//$ct = $request->getHeaderLine('Accept');
    	return Core::error($this->container, $response, $exception, $this->debug);
	}
}
