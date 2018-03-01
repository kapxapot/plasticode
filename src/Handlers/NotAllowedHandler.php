<?php

namespace Plasticode\Handlers;

use Plasticode\Contained;
use Plasticode\Exceptions\AuthenticationException;

class NotAllowedHandler extends Contained {
	public function __invoke($request, $response) {
    	$ex = new AuthenticationException('Method not allowed.');
    	return Core::error($this->container, $response, $ex);
	}
}
