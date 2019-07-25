<?php

namespace Plasticode\Handlers;

use Plasticode\Contained;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\AuthenticationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotAllowedHandler extends Contained
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
    {
        $ex = new AuthenticationException('Method not allowed.');
        return Response::error($this->container, $request, $response, $ex);
    }
}
