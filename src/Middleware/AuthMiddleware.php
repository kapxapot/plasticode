<?php

namespace Plasticode\Middleware;

use Plasticode\Auth\Auth;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;

class AuthMiddleware extends HomeMiddleware
{
    /** @var Auth */
    private $auth;

    public function __construct(
        RouterInterface $router,
        Auth $auth,
        string $home
    )
    {
        parent::__construct($router, $home);

        $this->auth = $auth;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Response $response,
        $next
    ) : ResponseInterface
    {
        if (!$this->auth->check()) {
            return $response->withRedirect($this->homePath);
        }

        $response = $next($request, $response);
        
        return $response;
    }
}
