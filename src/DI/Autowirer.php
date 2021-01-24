<?php

namespace Plasticode\DI;

use Exception;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;

/**
 * Automatic object creator using container.
 */
class Autowirer
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    )
    {
        $this->container = $container;
    }

    /**
     * Creates an object based on container definitions and registers it in container is case of success.
     * 
     * In case of failure throws {@see InvalidConfigurationException}.
     * 
     * @return mixed
     * @throws InvalidConfigurationException
     */
    public function autowire(string $className)
    {
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
            return $class->newInstanceWithoutConstructor();
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

                throw new InvalidConfigurationException(
                    'Can\'t autowire parameter [' . $param->getPosition() . '] ' .
                    '"' . $param->getName() . '" for class ' . $className . ', ' .
                    'provide a typehint or make it nullable.'
                );
            }

            $paramClassName = $paramType->getName();

            // try get the param from the container
            try {
                $args[] = $this->container->get($paramClassName);
                continue;
            } catch (Exception $ex) {
                // or set it to null if it's nullable
                if ($paramType->allowsNull()) {
                    $args[] = null;
                    continue;
                }

                throw $ex;
            }

            throw new InvalidConfigurationException(
                'Can\'t autowire parameter [' . $param->getPosition() . '] ' .
                '"' . $param->getName() . '" for class ' . $className . ', ' .
                'it can\'t be found in the container and is not nullable (add it to the container or make nullable).'
            );
        }

        return $class->newInstanceArgs($args);
    }
}
