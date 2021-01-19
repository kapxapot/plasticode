<?php

namespace Plasticode\Mapping;

use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Psr\Container\ContainerInterface;

abstract class MappingAggregator
{
    /** @var MappingProviderInterface[] */
    protected array $mappingProviders = [];

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

        // convert aliases to mappings
        foreach ($this->mergeAliases() as $alias => $key) {
            $mappings[$alias] = fn (ContainerInterface $c) => $c->get($key);
        }

        // add generators
        $mappings = array_merge($mappings, $this->mergeGenerators());

        return $mappings;
    }

    /**
     * Wires up the container and boots all providers.
     */
    public function boot(ContainerInterface $container): void
    {
        $this->wireUpContainer($container);

        foreach ($this->mappingProviders as $provider) {
            $provider->boot($container);
        }
    }

    /**
     * Get mappings and add them to container.
     */
    abstract protected function wireUpContainer(ContainerInterface $container): void;

    private function mergeMappings(): array
    {
        return $this->mergeArrays(
            fn (MappingProviderInterface $p) => $p->getMappings()
        );
    }

    private function mergeAliases(): array
    {
        return $this->mergeArrays(
            fn (MappingProviderInterface $p) => $p->getAliases()
        );
    }

    private function mergeGenerators(): array
    {
        return $this->mergeArrays(
            fn (MappingProviderInterface $p) => $p->getGenerators()
        );
    }

    private function mergeArrays(callable $extractArray): array
    {
        $accumulator = [];

        foreach ($this->mappingsProviders as $provider) {
            $accumulator = array_merge(
                $accumulator,
                $extractArray($provider)
            );
        }

        return $accumulator;
    }
}
