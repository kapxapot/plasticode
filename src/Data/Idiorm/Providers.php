<?php

namespace Plasticode\Data\Idiorm;

use Plasticode\Collections\MappingProviderCollection;
use Plasticode\Data\Idiorm\Providers\DatabaseProvider;
use Plasticode\Data\Idiorm\Providers\RepositoryProvider;

class Providers extends MappingProviderCollection
{
    public function __construct()
    {
        parent::__construct(
            [
                new DatabaseProvider(),
                new RepositoryProvider()
            ]
        );
    }
}
