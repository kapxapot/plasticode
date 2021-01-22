<?php

namespace Plasticode\Mapping\Providers\Generic;

use Plasticode\Events\EventDispatcher;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Psr\Container\ContainerInterface;

abstract class MappingProvider implements MappingProviderInterface
{
    public function getMappings(): array
    {
        return [];
    }

    public function getFactories(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }

    public function getGenerators(): array
    {
        return [];
    }

    public function getEventHandlers(): array
    {
        return [];
    }

    /**
     * Registers event handlers.
     */
    public function boot(ContainerInterface $container): void
    {
        $this->registerEventHandlers($container);
    }

    private function registerEventHandlers(ContainerInterface $c): void
    {
        /** @var EventDispatcher */
        $dispatcher = $c->get(EventDispatcher::class);

        $dispatcher->addHandlers(
            ...$this->getEventHandlers()
        );
    }
}
