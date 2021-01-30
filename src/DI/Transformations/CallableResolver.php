<?php

namespace Plasticode\DI\Transformations;

use Plasticode\DI\Autowirer;
use Plasticode\DI\Interfaces\TransformationInterface;
use Psr\Container\ContainerInterface;

class CallableResolver implements TransformationInterface
{
    private Autowirer $autowirer;

    public function __construct(Autowirer $autowirer)
    {
        $this->autowirer = $autowirer;
    }

    public function __invoke(ContainerInterface $container, object $object): object
    {
        if (!is_callable($object)) {
            return $object;
        }

        return $this->autowirer->autowireCallable($container, $object);
    }
}
