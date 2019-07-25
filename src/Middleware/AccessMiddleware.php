<?php

namespace Plasticode\Middleware;

use Plasticode\Exceptions\Http\AuthenticationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccessMiddleware extends Middleware
{
    private $entity;
    private $action;
    private $redirect;
    
    public function __construct(ContainerInterface $container, string $entity, string $action, string $redirect = null)
    {
        parent::__construct($container);
        
        $this->entity = $entity;
        $this->action = $action;
        $this->redirect = $redirect;
    }
    
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $next)
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
