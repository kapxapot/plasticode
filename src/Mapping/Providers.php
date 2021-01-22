<?php

namespace Plasticode\Mapping;

use Plasticode\Collections\MappingProviderCollection;
use Plasticode\Mapping\Providers\CoreProvider;
use Plasticode\Mapping\Providers\ExternalProvider;
use Plasticode\Mapping\Providers\GeneratorProvider;
use Plasticode\Mapping\Providers\ParsingProvider;
use Plasticode\Mapping\Providers\ServiceProvider;
use Plasticode\Mapping\Providers\SlimProvider;
use Plasticode\Mapping\Providers\ValidationProvider;

class Providers extends MappingProviderCollection
{
    public function __construct(array $settings)
    {
        parent::__construct(
            [
                new SlimProvider(),
                new CoreProvider($settings),
                new GeneratorProvider(),
                new ValidationProvider(),
                new ParsingProvider(),
                new ServiceProvider(),
                new ExternalProvider()
            ]
        );
    }
}
