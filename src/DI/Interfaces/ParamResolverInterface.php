<?php

namespace Plasticode\DI\Interfaces;

use Psr\Container\ContainerInterface;
use ReflectionParameter;

interface ParamResolverInterface
{
    function __invoke(ContainerInterface $container, ReflectionParameter $param): ?object;
}
