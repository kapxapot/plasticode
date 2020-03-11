<?php

namespace Plasticode\Handlers;

use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\AuthenticationException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotAllowedHandler
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $ex = new AuthenticationException('Method not allowed.');

        return Response::error(
            $this->container,
            $request,
            $response,
            $ex
        );
    }
}
