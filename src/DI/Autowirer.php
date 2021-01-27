<?php

namespace Plasticode\DI;

use Exception;
use Plasticode\DI\Interfaces\ParamResolverInterface;
use Plasticode\Exceptions\InvalidConfigurationException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use Webmozart\Assert\Assert;

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
    /** @var ParamResolverInterface[] */
    protected array $untypedParamResolvers = [];

    /**
     * @return $this
     */
    public function withUntypedParamResolver(ParamResolverInterface $resolver): self
    {
        $this->untypedParamResolvers[] = $resolver;

        return $this;
    }

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
                'Class "' . $className . '" doesn\'t exist and can\'t be autowired.'
            );
        }

        $class = new ReflectionClass($className);

        // check for interface & abstract class
        // they can't be instantiated
        if ($class->isAbstract() || $class->isInterface()) {
            throw new InvalidConfigurationException(
                'Can\'t autowire class "' . $className . '", ' .
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
        $args = $this->autoParamFactories($container, $params);

        return fn (ContainerInterface $c) =>
            $class->newInstanceArgs(
                array_map(
                    fn (?callable $argFunc) => $argFunc ? ($argFunc)($c) : null,
                    $args
                )
            );
    }

    /**
     * @param ReflectionParameter[] $params
     * @return object[]
     */
    public function autowireParams(ContainerInterface $container, array $params): array
    {
        return array_map(
            fn (?callable $paramFactory) => $paramFactory
                ? ($paramFactory)($container)
                : null,
            $this->autoParamFactories($container, $params)
        );
    }

    /**
     * Resolves the params list based on the container's definitions as callables.
     * 
     * @param ReflectionParameter[] $params
     * @return callable[]
     */
    public function autoParamFactories(ContainerInterface $container, array $params): array
    {
        return array_map(
            fn (ReflectionParameter $param) =>
                $this->autoParamFactory($container, $param),
            $params
        );
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function autoParamFactory(
        ContainerInterface $container,
        ReflectionParameter $param
    ): ?callable
    {
        /** @var ReflectionNamedType */
        $paramType = $param->getType();

        // no typehint => such a param can't be autowired
        // if it's nullable, set null, otherwise throw an exception
        if (is_null($paramType)) {
            $object = $this->tryResolveUntypedParam($container, $param);

            if ($object !== null) {
                return $object;
            }

            if ($param->allowsNull()) {
                return null;
            }

            throw new InvalidConfigurationException(
                'Can\'t autowire parameter [' . $param->getPosition() . '] ' .
                '"' . $param->getName() . '", ' .
                'provide a typehint or make it nullable.'
            );
        }

        $paramClassName = $paramType->getName();

        // check if the container is able to provide the param
        if ($container->has($paramClassName)) {
            return function (ContainerInterface $c) use ($paramClassName) {
                $arg = $c->get($paramClassName);

                Assert::isInstanceOf($arg, $paramClassName);

                return $arg;
            };
        }

        if ($paramType->allowsNull()) {
            // or set it to null if it's nullable
            return null;
        }

        throw new InvalidConfigurationException(
            'Can\'t autowire parameter [' . $param->getPosition() . '] ' .
            '"' . $param->getName() . '" of class "' . $paramClassName . '", ' .
            'it can\'t be found in the container and is not nullable (add it to the container or make nullable).'
        );
    }

    protected function tryResolveUntypedParam(
        ContainerInterface $container,
        ReflectionParameter $param
    ): ?object
    {
        foreach ($this->untypedParamResolvers as $resolver) {
            $object = ($resolver)($container, $param);

            if ($object !== null) {
                return $object;
            }
        }

        return null;
    }
}
