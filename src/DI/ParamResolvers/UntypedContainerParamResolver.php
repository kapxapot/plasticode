<?php

namespace Plasticode\DI\ParamResolvers;

use Plasticode\DI\Interfaces\ParamResolverInterface;
use Psr\Container\ContainerInterface;
use ReflectionParameter;

/**
 * Resolves untyped `$container` parameter to {@see ContainerInterface}.
 */
class UntypedContainerParamResolver implements ParamResolverInterface
{
    public function __invoke(
        ContainerInterface $container,
        ReflectionParameter $param
    ): ?object
    {
        if ($param->getName() === 'container') {
            return $container;
        }

        return null;
    }
}
