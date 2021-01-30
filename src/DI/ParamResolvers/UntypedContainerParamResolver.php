<?php

namespace Plasticode\DI\ParamResolvers;

use Plasticode\DI\Interfaces\ParamFactoryResolverInterface;
use Psr\Container\ContainerInterface;
use ReflectionParameter;

/**
 * Checks the parameter's name and if it's `container`, then returns a factory
 * for it's resolution to {@see ContainerInterface}.
 */
class UntypedContainerParamResolver implements ParamFactoryResolverInterface
{
    public function __invoke(
        ContainerInterface $container,
        ReflectionParameter $param
    ): ?callable
    {
        if ($param->getName() === 'container') {
            return fn () => $container;
        }

        return null;
    }
}
