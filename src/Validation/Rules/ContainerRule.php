<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerInterface;
use Respect\Validation\Rules\AbstractRule;

abstract class ContainerRule extends AbstractRule
{
    protected $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }
    
    public function validate(string $input)
    {
        if (!isset($this->container)) {
            throw new InvalidConfigurationException('Container not found!');
        }
    }
}
