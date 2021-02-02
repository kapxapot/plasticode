<?php

namespace Plasticode\Mapping\Interfaces;

use Psr\Container\ContainerInterface;

interface MappingProviderInterface
{
    function getMappings(): array;

    function getEventHandlers(ContainerInterface $container): array;

    function boot(ContainerInterface $container): void;
}
