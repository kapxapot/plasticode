<?php

namespace Plasticode\Middleware;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Exceptions\Http\AuthenticationException;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response as SlimResponse;
use Slim\Interfaces\RouterInterface;

class AccessMiddleware
{
    private Access $access;
    private AuthInterface $auth;
    private RouterInterface $router;

    private string $entity;
    private string $action;
    private ?string $redirect = null;
    
    public function __construct(
        Access $access,
        AuthInterface $auth,
        RouterInterface $router,
        string $entity,
        string $action,
        ?string $redirect = null
    )
    {
        $this->access = $access;
        $this->auth = $auth;
        $this->router = $router;

        $this->entity = $entity;
        $this->action = $action;
        $this->redirect = $redirect;
    }
    
    public function __invoke(
        ServerRequestInterface $request,
        SlimResponse $response,
        $next
    )
    {
        $user = $this->auth->getUser();

        $hasRights = $this->access->checkRights(
            $this->entity,
            $this->action,
            $user
        );

        if ($hasRights) {
            return $next($request, $response);
        }

        if ($this->redirect) {
            return $response->withRedirect(
                $this->router->pathFor($this->redirect)
            );
        }

        throw new AuthenticationException();
    }
}
