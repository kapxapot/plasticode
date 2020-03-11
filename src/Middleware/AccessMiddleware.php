<?php

namespace Plasticode\Middleware;

use Plasticode\Auth\Access;
use Plasticode\Exceptions\Http\AuthenticationException;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Response as SlimResponse;
use Slim\Interfaces\RouterInterface;

class AccessMiddleware
{
    /** @var Access */
    private $access;

    /** @var RouterInterface */
    private $router;

    private $entity;
    private $action;
    private $redirect;
    
    public function __construct(
        Access $access,
        RouterInterface $router,
        string $entity,
        string $action,
        string $redirect = null
    )
    {
        $this->access = $access;
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
        if ($this->access->checkRights($this->entity, $this->action)) {
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
