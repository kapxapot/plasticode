<?php

namespace Plasticode\DI;

use Exception;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

/**
 * The automatic object creator (aka "the abstract factory") that uses the container's definitions.
 * 
 * Can:
 * 
 * - Return a created object.
 * - Return a callable (a factory) that creates an object.
 * - Check if an object can be created.
 */
class Autowirer
{
    /**
     * Creates an object based on the container's definitions.
     * 
     * - If unable to autowire, throws {@see InvalidConfigurationException}.
     * - If the object's creation fails, throws a generic {@see Exception}.
     * 
     * @throws InvalidConfigurationException
     * @throws Exception
     */
    public function autowire(
        ContainerInterface $container,
        string $className
    ): object
    {
        $factory = $this->autoFactory($container, $className);

        return ($factory)($container);
    }

    /**
     * Checks if an object can be created based on the container's definitions.
     */
    public function canAutowire(
        ContainerInterface $container,
        string $className
    ): bool
    {
        try {
            // if a factory can't be created, the exception is thrown
            $this->autoFactory($container, $className);

            return true;
        } catch (InvalidConfigurationException $ex) {
            return false;
        }
    }

    /**
     * Creates a callable (a factory) that creates an object based on container's definitions.
     * 
     * In case of failure throws {@see InvalidConfigurationException}.
     * 
     * @throws InvalidConfigurationException
     */
    public function autoFactory(
        ContainerInterface $container,
        string $className
    ): callable
    {
        if (!class_exists($className)) {
            throw new InvalidConfigurationException(
                'Class ' . $className . ' doesn\'t exist and can\'t be autowired.'
            );
        }

        $class = new ReflectionClass($className);

        // check for interface & abstract class
        // they can't be instantiated
        if ($class->isAbstract() || $class->isInterface()) {
            throw new InvalidConfigurationException(
                'Can\'t autowire class ' . $className . ', ' .
                'because it\'s an interface or an abstract class and is not defined in the container.'
            );
        }

        $constructor = $class->getConstructor();

        // no constructor, just create an object
        if (is_null($constructor)) {
            return fn (ContainerInterface $c) =>
                $class->newInstanceWithoutConstructor();
        }

        $params = $constructor->getParameters();

        $args = [];

        foreach ($params as $param) {
            /** @var ReflectionNamedType */
            $paramType = $param->getType();

            // no typehint => such a param can't be autowired
            // if it's nullable, set null, otherwise throw an exception
            if (is_null($paramType)) {
                if ($param->allowsNull()) {
                    $args[] = null;
                    continue;
                }

                throw new InvalidConfigurationException(
                    'Can\'t autowire parameter [' . $param->getPosition() . '] ' .
                    '"' . $param->getName() . '" for class ' . $className . ', ' .
                    'provide a typehint or make it nullable.'
                );
            }

            $paramClassName = $paramType->getName();

            // check if the container is able to provide the param
            if ($container->has($paramClassName)) {
                $args[] = fn (ContainerInterface $c) => $c->get($paramClassName);
                continue;
            } elseif ($paramType->allowsNull()) {
                // or set it to null if it's nullable
                $args[] = null;
                continue;
            }

            throw new InvalidConfigurationException(
                'Can\'t autowire parameter [' . $param->getPosition() . '] ' .
                '"' . $param->getName() . '" for class ' . $className . ', ' .
                'it can\'t be found in the container and is not nullable (add it to the container or make nullable).'
            );
        }

        return fn (ContainerInterface $c) =>
            $class->newInstanceArgs(
                array_map(
                    fn (?callable $argFunc) => $argFunc ? ($argFunc)($c) : null,
                    $args
                )
            );
    }
}
