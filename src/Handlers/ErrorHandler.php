<?php

namespace Plasticode\Handlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Plasticode\Contained;
use Plasticode\Core\Response;

class ErrorHandler extends Contained
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception) : ResponseInterface
    {
        return Response::error($this->container, $request, $response, $exception);
    }
}
