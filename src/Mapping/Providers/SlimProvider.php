<?php

namespace Plasticode\Mapping\Providers;

use Plasticode\Handlers\ErrorHandler;
use Plasticode\Handlers\Interfaces\ErrorHandlerInterface;
use Plasticode\Handlers\Interfaces\NotAllowedHandlerInterface;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Handlers\NotAllowedHandler;
use Plasticode\Handlers\NotFoundHandler;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouterInterface;

/**
 * Provides native Slim mappings.
 */
class SlimProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            // Slim -> Plasticode
            'errorHandler' => ErrorHandlerInterface::class,
            'notAllowedHandler' => NotAllowedHandlerInterface::class,
            'notFoundHandler' => NotFoundHandlerInterface::class,

            // Plasticode aliases
            ErrorHandlerInterface::class => ErrorHandler::class,
            NotAllowedHandlerInterface::class => NotAllowedHandler::class,
            NotFoundHandlerInterface::class => NotFoundHandler::class,

            // Plasticode -> Slim
            RouterInterface::class => 'router',
            ServerRequestInterface::class => 'request',
        ];
    }
}
