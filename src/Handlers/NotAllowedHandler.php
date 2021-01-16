<?php

namespace Plasticode\Handlers;

use Plasticode\Core\AppContext;
use Plasticode\Core\Response;
use Plasticode\Exceptions\Http\AuthenticationException;
use Plasticode\Handlers\Interfaces\NotAllowedHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class NotAllowedHandler implements NotAllowedHandlerInterface
{
    private AppContext $appContext;

    public function __construct(AppContext $appContext)
    {
        $this->appContext = $appContext;
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response
    ) : ResponseInterface
    {
        $ex = new AuthenticationException('Method not allowed.');

        return Response::error(
            $this->appContext,
            $request,
            $response,
            $ex
        );
    }
}
