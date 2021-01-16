<?php

namespace Plasticode\Handlers\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface NotFoundHandlerInterface
{
    function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface;
}
