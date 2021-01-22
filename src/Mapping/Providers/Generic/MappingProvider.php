<?php

namespace Plasticode\Mapping\Providers\Generic;

use Plasticode\Events\EventDispatcher;
use Plasticode\Generators\Core\GeneratorResolver;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Psr\Container\ContainerInterface;

abstract class MappingProvider implements MappingProviderInterface
{
    public function getMappings(): array
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
     * Registers generators and event handlers.
     */
    public function boot(ContainerInterface $container): void
    {
        $this->registerGenerators($container);
        $this->registerEventHandlers($container);
    }

    private function registerGenerators(ContainerInterface $container): void
    {
        /** @var GeneratorResolver */
        $resolver = $container->get(GeneratorResolver::class);

        $resolver->register(
            ...array_values($this->getGenerators())
        );
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
