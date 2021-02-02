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

    /**
     * Returns aggregated mappings from all providers.
     */
    protected function mergeMappings(): array
    {
        return $this->mergeArrays(
            fn (MappingProviderInterface $p) => $p->getMappings()
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
