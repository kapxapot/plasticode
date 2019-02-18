<?php

namespace Plasticode\Middleware;

class HomeMiddleware extends Middleware
{
	protected $homePath;

	public function __construct($container, $home)
	{
		parent::__construct($container);
		
		$this->homePath = $this->router->pathFor($home);
	}
}
