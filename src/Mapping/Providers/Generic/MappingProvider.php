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

    public function getEventHandlers(ContainerInterface $container): array
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

    private function registerEventHandlers(ContainerInterface $container): void
    {
        /** @var EventDispatcher */
        $dispatcher = $container->get(EventDispatcher::class);

        $dispatcher->addHandlers(
            ...$this->getEventHandlers($container)
        );
    }
}
