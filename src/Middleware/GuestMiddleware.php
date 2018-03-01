<?php

namespace Plasticode\Middleware;

class GuestMiddleware extends HomeMiddleware {
	public function __invoke($request, $response, $next) {
		if ($this->auth->check()) {
			return $response->withRedirect($this->homePath);
		}

		$response = $next($request, $response);
		
		return $response;
	}
}
