<?php

namespace Plasticode\DI\Interfaces;

use Psr\Container\ContainerInterface;
use ReflectionParameter;

interface ParamFactoryResolverInterface
{
    public function __invoke(ContainerInterface $container, ReflectionParameter $param): ?callable;
}
