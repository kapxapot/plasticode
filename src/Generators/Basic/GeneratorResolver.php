<?php

namespace Plasticode\Generators;

use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Generators\Interfaces\EntityGeneratorInterface;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;

class GeneratorResolver
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    /**
     * @param string $entity Entity name in plural form, snake case ('auth_tokens').
     */
    public function resolve(string $entity) : EntityGeneratorInterface
    {
        // Auth_Tokens => auth_tokens
        $entity = mb_strtolower($entity);

        // auth_tokens => authTokens
        $pascalEntity = Strings::toPascalCase($entity);

        $name = $pascalEntity . 'Generator';

        $generator = $this->container[$name] ?? null;

        if (is_null($generator)) {
            throw new InvalidConfigurationException(
                'Generator not found in container: ' . $name
            );
        }

        if (!($generator instanceof EntityGeneratorInterface)) {
            throw new InvalidConfigurationException(
                'Not an EntityGeneratorInterface: ' . $name
            );
        }

        return $generator;
    }
}
