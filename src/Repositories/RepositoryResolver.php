<?php

namespace Plasticode\Repositories;

use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Repositories\Interfaces\Basic\RepositoryInterface;
use Psr\Container\ContainerInterface;

class RepositoryResolver
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    /**
     * @param string $entity Entity name in camel case ('authToken').
     * @throws InvalidConfigurationException
     */
    public function resolve(string $entity) : RepositoryInterface
    {
        $name = $entity . 'Repository';

        $repository = $this->container[$name] ?? null;

        if (is_null($repository)) {
            throw new InvalidConfigurationException(
                'Repository not found: ' . $name
            );
        }

        if (!($repository instanceof RepositoryInterface)) {
            throw new InvalidConfigurationException(
                'Not a RepositoryInterface: ' . $name
            );
        }

        return $repository;
    }
}
