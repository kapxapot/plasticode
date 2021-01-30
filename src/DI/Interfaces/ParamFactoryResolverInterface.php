<?php

namespace Plasticode\DI\Interfaces;

use Psr\Container\ContainerInterface;
use ReflectionParameter;

interface ParamFactoryResolverInterface
{
    function __invoke(ContainerInterface $container, ReflectionParameter $param): ?callable;
}
