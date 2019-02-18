<?php

namespace Plasticode\Middleware;

use Plasticode\Exceptions\AuthenticationException;

class AccessMiddleware extends Middleware
{
	private $entity;
	private $action;
	private $redirect;
	
	public function __construct($container, $entity, $action, $redirect = null)
	{
		parent::__construct($container);
		
		$this->entity = $entity;
		$this->action = $action;
		$this->redirect = $redirect;
	}
	
	public function __invoke($request, $response, $next)
	{
		if ($this->access->checkRights($this->entity, $this->action)) {
			$response = $next($request, $response);
		}
		elseif ($this->redirect) {
			return $response->withRedirect($this->router->pathFor($this->redirect));
		}
		else {
			throw new AuthenticationException();
		}

		return $response;
	}
}
