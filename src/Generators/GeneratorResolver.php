<?php

namespace Plasticode\Generators;

use Plasticode\Contained;
use Plasticode\Exceptions\ApplicationException;
use Plasticode\Util\Strings;

class GeneratorResolver extends Contained {
	protected $namespaces;
	
	/**
	 * Create GeneratorResolver.
	 * 
	 * @param ContainerInterface $container
	 * @param string[] $namespaces Namespaces to search generators
	 */
	public function __construct($container, $namespaces) {
		parent::__construct($container);
		
		$this->namespaces = $namespaces ?? [];
		$this->namespaces[] = __NAMESPACE__;
	}
	
	private function buildClassName($namespace, $name) {
		return $namespace . '\\' . $name . 'Generator';
	}
	
	private function resolve($name) {
		foreach ($this->namespaces as $namespace) {
			$generatorClass = $this->buildClassName($namespace, $name);
			if (class_exists($generatorClass)) {
				break;
			}
		}
		
		return $generatorClass;
	}
	
	public function resolveEntity($entity) {
		$pascalEntity = Strings::toPascalCase($entity);
		$generatorClass = $this->resolve($pascalEntity);

		if (!class_exists($generatorClass)) {
			$generatorClass = $this->resolve('Entity');
		}
		
		if (!class_exists($generatorClass)) {
			throw new ApplicationException("Unable to resolve {$entity} generator class.");
		}
		
		return new $generatorClass($this->container, $entity);
	}
}
