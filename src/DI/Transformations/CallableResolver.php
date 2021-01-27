<?php

namespace Plasticode\DI\Transformations;

use Closure;
use Plasticode\DI\Autowirer;
use Plasticode\DI\Interfaces\TransformationInterface;
use Psr\Container\ContainerInterface;
use ReflectionFunction;

class CallableResolver implements TransformationInterface
{
    public function __invoke(ContainerInterface $container, object $object): object
    {
        if (!isCallable($object)) {
            return $object;
        }

        /** @var callable */
        $callable = $object;

        $closure = Closure::fromCallable($callable);
        $function = new ReflectionFunction($closure);
        $params = $function->getParameters();

        $autowirer = new Autowirer();
        $args = $autowirer->autowireParams($container, $params);

        return ($callable)(...$args);
    }
}
