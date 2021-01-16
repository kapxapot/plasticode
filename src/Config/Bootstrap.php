<?php

namespace Plasticode\Config;

use Plasticode\Interfaces\MappingProviderInterface;
use Psr\Container\ContainerInterface;

class Bootstrap implements MappingProviderInterface
{
    protected array $settings;

    /** @var MappingProviderInterface[] */
    protected array $mappingProviders = [];

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Registers mapping providers.
     * 
     * @return $this
     */
    public function register(MappingProviderInterface ...$mappingProviders): self
    {
        foreach ($mappingProviders as $mp) {
            $this->mappingProviders[] = $mp;
        }

        return $this;
    }

    /**
     * - Fill container.
     */
    public function boot(ContainerInterface $container): void
    {
        foreach ($this->aggregateMappings() as $key => $value) {
            $container[$key] = $value;
        }
    }

    /**
     * Aggregates the own mappings with all mappings from providers.
     */
    private function aggregateMappings(): array
    {
        $mappings = $this->getMappings();

        foreach ($this->mappingsProviders as $provider) {
            $mappings = array_merge(
                $mappings,
                $provider->getMappings()
            );
        }

        return $mappings;
    }

    /**
     * Get mappings for DI container. Add more mappings in overrides.
     */
    public function getMappings(): array
    {
        return [];
    }
}
