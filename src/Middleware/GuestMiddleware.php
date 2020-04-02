<?php

namespace Plasticode\Middleware;

use Plasticode\Services\AuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response;
use Slim\Interfaces\RouterInterface;

class GuestMiddleware extends HomeMiddleware
{
    private AuthService $authService;

    public function __construct(
        RouterInterface $router,
        AuthService $authService,
        string $home
    )
    {
        parent::__construct($router, $home);

        $this->authService = $authService;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Response $response,
        $next
    ) : ResponseInterface
    {
        if ($this->authService->check()) {
            return $response->withRedirect($this->homePath);
        }

        $response = $next($request, $response);
        
        return $response;
    }
}
