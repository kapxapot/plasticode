<?php

namespace Plasticode\DI;

use Exception;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use Webmozart\Assert\Assert;

/**
 * Automatic object creator using container.
 */
class Autowirer
{
    /**
     * Creates an object based on container definitions.
     * 
     * In case of failure throws {@see InvalidConfigurationException}.
     * 
     * @throws InvalidConfigurationException
     */
    public function autowire(
        ContainerInterface $container,
        string $className
    ): object
    {
        /** @var object */
        $result = $this->autowirePass(true, $container, $className);

        Assert::object($result);

        return $result;
    }

    /**
     * Checks if an object can be created based on container definitions.
     */
    public function canAutowire(
        ContainerInterface $container,
        string $className
    ): bool
    {
        /** @var boolean */
        $result = $this->autowirePass(false, $container, $className);

        Assert::boolean($result);

        return $result;
    }

    /**
     * Inner implementation of autowire that allows to run the algorithm with
     * the object instantiation or without it (test run that checks the possibility of object creation).
     * 
     * @return object|boolean
     * 
     * @throws InvalidConfigurationException
     */
    private function autowirePass(
        bool $instantiate,
        ContainerInterface $container,
        string $className
    )
    {
        if (!class_exists($className)) {
            if (!$instantiate) {
                return false;
            }

            throw new InvalidConfigurationException(
                'Class ' . $className . ' doesn\'t exist and can\'t be autowired.'
            );
        }

        $class = new ReflectionClass($className);

        // check for interface & abstract class
        // they can't be instantiated
        if ($class->isAbstract() || $class->isInterface()) {
            if (!$instantiate) {
                return false;
            }

            throw new InvalidConfigurationException(
                'Can\'t autowire class ' . $className . ', ' .
                'because it\'s an interface or an abstract class and is not defined in the container.'
            );
        }

        $constructor = $class->getConstructor();

        // no constructor, just create an object
        if (is_null($constructor)) {
            return $instantiate
                ? $class->newInstanceWithoutConstructor()
                : true;
        }

        $params = $constructor->getParameters();

        $args = [];

        foreach ($params as $param) {
            /** @var ReflectionNamedType */
            $paramType = $param->getType();

            // no typehint
            // such a param can't be autowired
            // if it's nullable, set null, otherwise throw an exception
            if (is_null($paramType)) {
                if ($param->allowsNull()) {
                    $args[] = null;
                    continue;
                }

                if (!$instantiate) {
                    return false;
                }

                throw new InvalidConfigurationException(
                    'Can\'t autowire parameter [' . $param->getPosition() . '] ' .
                    '"' . $param->getName() . '" for class ' . $className . ', ' .
                    'provide a typehint or make it nullable.'
                );
            }

            $paramClassName = $paramType->getName();

            // try get the param from the container
            if ($container->has($paramClassName)) {
                $args[] = $container->get($paramClassName);
                continue;
            } elseif ($paramType->allowsNull()) {
                // or set it to null if it's nullable
                $args[] = null;
                continue;
            }

            if (!$instantiate) {
                return false;
            }

            throw new InvalidConfigurationException(
                'Can\'t autowire parameter [' . $param->getPosition() . '] ' .
                '"' . $param->getName() . '" for class ' . $className . ', ' .
                'it can\'t be found in the container and is not nullable (add it to the container or make nullable).'
            );
        }

        return $instantiate
            ? $class->newInstanceArgs($args)
            : true;
    }
}
