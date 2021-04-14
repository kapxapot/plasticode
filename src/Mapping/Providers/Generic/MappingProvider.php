<?php

namespace Plasticode\Mapping\Providers\Generic;

use Plasticode\Events\EventDispatcher;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\ObjectProxy;
use Psr\Container\ContainerInterface;

abstract class MappingProvider implements MappingProviderInterface
{
    public function getMappings(): array
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

    /**
     * Builds {@see ObjectProxy} for the provided `$className` retrieved from the container.
     */
    protected function proxy(ContainerInterface $container, string $className): ObjectProxy
    {
        return new ObjectProxy(
            fn () => $container->get($className)
        );
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
