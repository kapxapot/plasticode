<?php

namespace Plasticode\Config\MappingProviders;

use Plasticode\Core\AppContext;
use Plasticode\Handlers\ErrorHandler;
use Plasticode\Handlers\Interfaces\ErrorHandlerInterface;
use Plasticode\Handlers\Interfaces\NotAllowedHandlerInterface;
use Plasticode\Handlers\Interfaces\NotFoundHandlerInterface;
use Plasticode\Handlers\NotAllowedHandler;
use Plasticode\Handlers\NotFoundHandler;
use Plasticode\Interfaces\MappingProviderInterface;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;

class SlimProvider implements MappingProviderInterface
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

            // aliases

            RouterInterface::class =>
                fn (ContainerInterface $c) => $c->get('router'),

            NotFoundHandlerInterface::class =>
                fn (ContainerInterface $c) => $c->get('notFoundHandler'),

            ErrorHandlerInterface::class =>
                fn (ContainerInterface $c) => $c->get('errorHandler'),

            NotAllowedHandlerInterface::class =>
                fn (ContainerInterface $c) => $c->get('notAllowedHandler'),
        ];
    }
}
