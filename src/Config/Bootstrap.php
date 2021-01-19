<?php

namespace Plasticode\Config;

use Plasticode\Interfaces\MappingProviderInterface;

abstract class Bootstrap
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
        foreach ($mappingProviders as $mp) {
            $this->mappingProviders[] = $mp;
        }

        return $this;
    }

    /**
     * Returns aggregated mappings from the registered providers.
     */
    public function getMappings(): array
    {
        $mappings = [];

        foreach ($this->mappingsProviders as $provider) {
            $mappings = array_merge(
                $mappings,
                $provider->getMappings()
            );
        }

        return $mappings;
    }
}
