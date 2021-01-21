<?php

namespace Plasticode\Mapping\Interfaces;

use Psr\Container\ContainerInterface;

interface MappingProviderInterface
{
    function getMappings(): array;

    /**
     * @return array<string, string>
     */
    function getAliases(): array;

    function getGenerators(): array;

    /**
     * @return callable[]
     */
    function getEventHandlers(): array;

    function boot(ContainerInterface $container): void;
}