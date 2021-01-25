<?php

namespace Plasticode\DI\Interfaces;

use Psr\Container\ContainerInterface;

interface TransformationInterface
{
    function __invoke(ContainerInterface $container, object $obj): object;
}
