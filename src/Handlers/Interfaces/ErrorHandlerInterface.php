<?php

namespace Plasticode\Handlers\Interfaces;

use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ErrorHandlerInterface
{
    function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Exception $exception
    ): ResponseInterface;
}
