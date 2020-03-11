<?php

namespace Plasticode\Handlers;

use Plasticode\Core\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorHandler
{
    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        \Exception $exception
    ) : ResponseInterface
    {
        return Response::error(
            $this->container,
            $request,
            $response,
            $exception
        );
    }
}
