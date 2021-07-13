<?php

namespace Plasticode\DI\Containers;

use Plasticode\Exceptions\DI\NotFoundException;
use Psr\Container\ContainerInterface;

/**
 * This container allows adding sub-containers and sub-arrays with mappings
 * that act as fallback sources of mappings.
 * 
 * Notes:
 * 
 * - Sub-containers don't override the own container's mappings, they just add to it
 * and are used in case when the container can't resolve the requested key.
 * - Sub-containers are checked in the order of their addition.
 */
class AggregatingContainer extends ArrayContainer
{
    /** @var ContainerInterface[] */
    private array $subContainers = [];

    /**
     * @param ContainerInterface|array $container
     * @return static
     */
    public function withContainer($container): self
    {
        if (!$container instanceof ContainerInterface) {
            $container = new ArrayContainer($container);
        }

        $this->subContainers[] = $container;

        return $this;
    }

    // ContainerInterface overrides

    public function get($id)
    {
        if (parent::has($id)) {
            return parent::get($id);
        }

        foreach ($this->subContainers as $container) {
            if ($container->has($id)) {
                return $container->get($id);
            }
        }

        throw new NotFoundException(
            'Mapping for "' . $id . '" is not defined neither in the main container ' .
            'nor in any of the sub-containers (' . count($this->subContainers) . ').'
        );
    }

    public function has($id)
    {
        if (parent::has($id)) {
            return true;
        }

        foreach ($this->subContainers as $container) {
            if ($container->has($id)) {
                return true;
            }
        }

        return false;
    }
}
