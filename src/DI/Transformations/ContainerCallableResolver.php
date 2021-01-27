<?php

namespace Plasticode\DI\Transformations;

use Closure;
use Plasticode\DI\Interfaces\TransformationInterface;
use Psr\Container\ContainerInterface;
use ReflectionFunction;
use ReflectionNamedType;

class ContainerCallableResolver implements TransformationInterface
{
    public function __invoke(ContainerInterface $container, object $object): object
    {
        return $this->isContainerCallable($object)
            ? ($object)($container)
            : $object;
    }

    /**
     * Returns `true` if `$value` is callable with 1 parameter of type {@see ContainerInterface}.
     * 
     * @param mixed $value
     */
    public function isContainerCallable($value): bool
    {
        if (!isCallable($value)) {
            return false;
        }

        $closure = Closure::fromCallable($value);
        $function = new ReflectionFunction($closure);
        $params = $function->getParameters();

        if (count($params) !== 1) {
            return false;
        }

        $param = $params[0];

        /** @var ReflectionNamedType */
        $paramType = $param->getType();

        return $paramType !== null
            && $paramType->getName() === ContainerInterface::class;
    }
}
