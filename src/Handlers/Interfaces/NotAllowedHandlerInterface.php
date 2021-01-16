<?php

namespace Plasticode\Handlers\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface NotAllowedHandlerInterface
{
    function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface;
}
