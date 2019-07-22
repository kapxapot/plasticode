<?php

namespace Plasticode\Generators;

use Plasticode\Exceptions\ApplicationException;
use Plasticode\Util\Strings;

class GeneratorResolver
{
    protected $container;
    protected $namespaces;
    
    /**
     * Creates GeneratorResolver.
     * 
     * @param ContainerInterface $container
     * @param string[] $namespaces Namespaces to search generators
     */
    public function __construct($container, $namespaces)
    {
        $this->container = $container;
        $this->namespaces = $namespaces ?? [];
        $this->namespaces[] = __NAMESPACE__;
    }
    
    private function buildClassName($namespace, $name)
    {
        return $namespace . '\\' . $name . 'Generator';
    }
    
    private function resolve($name)
    {
        foreach ($this->namespaces as $namespace) {
            $generatorClass = $this->buildClassName($namespace, $name);
            if (class_exists($generatorClass)) {
                break;
            }
        }
        
        return $generatorClass;
    }
    
    public function resolveEntity($entity)
    {
        $entity = mb_strtolower($entity);
        
        $pascalEntity = Strings::toPascalCase($entity);
        $generatorClass = $this->resolve($pascalEntity);

        if (!class_exists($generatorClass)) {
            $generatorClass = $this->resolve('Entity');
        }
        
        if (!class_exists($generatorClass)) {
            throw new ApplicationException("Unable to resolve {$entity} generator class.");
        }
        
        $generator = new $generatorClass($this->container, $entity);

        return $generator;
    }
}
