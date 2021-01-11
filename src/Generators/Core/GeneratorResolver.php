<?php

namespace Plasticode\Generators\Core;

use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\Generators\Interfaces\EntityGeneratorInterface;

class GeneratorResolver
{
    private array $map = [];

    /**
     * @return $this
     */
    public function register(EntityGeneratorInterface ...$generators): self
    {
        foreach ($generators as $generator) {
            $entityClass = $generator->getEntityClass();
            $this->map[$entityClass] = $generator;
        }

        return $this;
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function resolve(string $entityClass): EntityGeneratorInterface
    {
        $generator = $this->map[$entityClass] ?? null;

        if (is_null($generator)) {
            throw new InvalidConfigurationException(
                'Generator not found for entity: ' . $entityClass
            );
        }

        return $generator;
    }
}
