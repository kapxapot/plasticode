<?php

namespace Plasticode\Mapping\Providers;

use Plasticode\Core\AppContext;
use Plasticode\Handlers\ErrorHandler;
use Plasticode\Handlers\Interfaces\ErrorHandlerInterface;
use Plasticode\Handlers\Interfaces\NotAllowedHandlerInterface;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Handlers\NotAllowedHandler;
use Plasticode\Handlers\NotFoundHandler;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;

/**
 * Provides native Slim mappings.
 */
class SlimProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            'notFoundHandler' =>
                fn (ContainerInterface $c) => new NotFoundHandler(
                    $c->get(AppContext::class)
                ),

            'errorHandler' =>
                fn (ContainerInterface $c) => new ErrorHandler(
                    $c->get(AppContext::class)
                ),

            'notAllowedHandler' =>
                fn (ContainerInterface $c) => new NotAllowedHandler(
                    $c->get(AppContext::class)
                ),
        ];
    }

    public function getAliases(): array
    {
        return [
            RouterInterface::class => 'router',
            NotFoundHandlerInterface::class => 'notFoundHandler',
            ErrorHandlerInterface::class => 'errorHandler',
            NotAllowedHandlerInterface::class => 'notAllowedHandler',
        ];
    }
}
