<?php

namespace Plasticode\Handlers;

use Plasticode\Contained;
use Plasticode\Core\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorHandler extends Contained
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, \Exception $exception) : ResponseInterface
    {
        return Response::error($this->container, $request, $response, $exception);
    }
}
