<?php

namespace Plasticode\Handlers;

use Exception;
use Plasticode\Core\AppContext;
use Plasticode\Core\Response;
use Plasticode\Handlers\Interfaces\ErrorHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ErrorHandler implements ErrorHandlerInterface
{
    private AppContext $appContext;

    public function __construct(AppContext $appContext)
    {
        $this->appContext = $appContext;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Exception $exception
    ): ResponseInterface
    {
        return Response::error(
            $this->appContext,
            $request,
            $response,
            $exception
        );
    }
}
