<?php

namespace Plasticode\Mapping\Aggregators;

use Plasticode\DI\Interfaces\ArrayContainerInterface;

class WritableMappingAggregator extends AbstractMappingAggregator
{
    public function __construct(
        ArrayContainerInterface $container
    )
    {
        parent::__construct($container);
    }

    protected function getContainer(): ArrayContainerInterface
    {
        return parent::getContainer();
    }

    protected function wireUpContainer(): void
    {
        $container = $this->getContainer();

        foreach ($this->getMappings() as $key => $value) {
            $container[$key] = $value;
        }
    }
}
