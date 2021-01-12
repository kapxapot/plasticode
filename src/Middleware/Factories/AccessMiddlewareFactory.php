<?php

namespace Plasticode\Middleware\Factories;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Middleware\AccessMiddleware;
use Slim\Interfaces\RouterInterface;

class AccessMiddlewareFactory
{
    private Access $access;
    private AuthInterface $auth;
    private RouterInterface $router;

    public function __construct(
        Access $access,
        AuthInterface $auth,
        RouterInterface $router
    )
    {
        $this->access = $access;
        $this->auth = $auth;
        $this->router = $router;
    }

    public function make(
        string $entity,
        string $action,
        ?string $redirect = null
    ): AccessMiddleware
    {
        return new AccessMiddleware(
            $this->access,
            $this->auth,
            $this->router,
            $entity,
            $action,
            $redirect
        );
    }
}
