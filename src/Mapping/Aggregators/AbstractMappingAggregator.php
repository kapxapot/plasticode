<?php

namespace Plasticode\Mapping\Aggregators;

use Plasticode\Collections\MappingProviderCollection;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractMappingAggregator
{
    private ContainerInterface $container;

    /** @var MappingProviderInterface[] */
    protected array $mappingProviders = [];

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    protected function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * Registers mapping provider collections.
     * 
     * @return $this
     */
    public function registerMany(
        MappingProviderCollection ...$mappingProviderCollections
    ): self
    {
        foreach ($mappingProviderCollections as $collection) {
            $this->register(...$collection);
        }

        return $this;
    }

    /**
     * Registers mapping providers.
     * 
     * @return $this
     */
    public function register(MappingProviderInterface ...$mappingProviders): self
    {
        foreach ($mappingProviders as $provider) {
            $this->mappingProviders[] = $provider;
        }

        return $this;
    }

    /**
     * Returns aggregated mappings from all providers.
     */
    public function getMappings(): array
    {
        $mappings = $this->mergeMappings();

        // resolve factories
        foreach ($this->mergeFactories() as $class => $factoryClass) {
            $mappings[$class] = new $factoryClass();
        }

        // convert aliases to mappings
        foreach ($this->mergeAliases() as $alias => $key) {
            $mappings[$alias] = fn (ContainerInterface $c) => $c->get($key);
        }

        return $mappings;
    }

    /**
     * Wires up the container and boots all providers.
     */
    public function boot(): void
    {
        $this->wireUpContainer();

        foreach ($this->mappingProviders as $provider) {
            $provider->boot(
                $this->getContainer()
            );
        }
    }

    /**
     * Get mappings and add them to container.
     */
    abstract protected function wireUpContainer(): void;

    private function mergeMappings(): array
    {
        return $this->mergeArrays(
            fn (MappingProviderInterface $p) => $p->getMappings()
        );
    }

    private function mergeFactories(): array
    {
        return $this->mergeArrays(
            fn (MappingProviderInterface $p) => $p->getFactories()
        );
    }

    private function mergeAliases(): array
    {
        return $this->mergeArrays(
            fn (MappingProviderInterface $p) => $p->getAliases()
        );
    }

    private function mergeArrays(callable $extractArray): array
    {
        $accumulator = [];

        foreach ($this->mappingProviders as $provider) {
            $accumulator = array_merge(
                $accumulator,
                $extractArray($provider)
            );
        }

        return $accumulator;
    }
}
