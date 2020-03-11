<?php

namespace Plasticode\Middleware;

use Slim\Interfaces\RouterInterface;

abstract class HomeMiddleware
{
    /** @var string */
    protected $homePath;

    public function __construct(RouterInterface $router, string $home)
    {
        $this->homePath = $router->pathFor($home);
    }
}
