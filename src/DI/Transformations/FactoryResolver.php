<?php

namespace Plasticode\DI\Transformations;

use Plasticode\DI\Interfaces\ContainerFactoryInterface;
use Plasticode\DI\Interfaces\TransformationInterface;
use Psr\Container\ContainerInterface;

class FactoryResolver implements TransformationInterface
{
    public function __invoke(ContainerInterface $container, object $obj): object
    {
        if ($obj instanceof ContainerFactoryInterface) {
            $obj = ($obj)($container);
        }

        return $obj;
    }
}
