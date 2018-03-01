<?php

namespace Plasticode\Handlers;

use Plasticode\Contained;
use Plasticode\Core\Core;

class ErrorHandler extends Contained {
	public function __invoke($request, $response, $exception) {
		// to do
		//$ct = $request->getHeaderLine('Accept');
    	return Core::error($this->container, $response, $exception);
	}
}
