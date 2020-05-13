<?php

namespace Plasticode\Generators;

use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;

class GeneratorResolver
{
    protected ContainerInterface $container;

    /** @var string[] */
    protected array $namespaces;

    /**
     * @param string[] $namespaces Namespaces to search generators in.
     */
    public function __construct(ContainerInterface $container, array $namespaces)
    {
        $this->container = $container;
        $this->namespaces = [...$namespaces, __NAMESPACE__];
    }

    private function buildClassName(string $namespace, string $name) : string
    {
        return $namespace . '\\' . $name . 'Generator';
    }

    private function resolve(string $name) : ?string
    {
        $generatorClass = null;

        foreach ($this->namespaces as $namespace) {
            $generatorClass = $this->buildClassName($namespace, $name);

            if (class_exists($generatorClass)) {
                break;
            }
        }

        return $generatorClass;
    }

    public function resolveEntity(string $entity) : EntityGenerator
    {
        $entity = mb_strtolower($entity);

        $pascalEntity = Strings::toPascalCase($entity);
        $generatorClass = $this->resolve($pascalEntity);

        if (!class_exists($generatorClass)) {
            $generatorClass = $this->resolve('Entity');
        }

        if (!class_exists($generatorClass)) {
            throw new InvalidConfigurationException(
                'Unable to resolve ' . $entity . ' generator class.'
            );
        }

        $generator = new $generatorClass(
            $this->container,
            $entity
        );

        return $generator;
    }
}
