<?php

namespace Plasticode\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use Plasticode\Exceptions\ApplicationException;

abstract class ContainerRule extends AbstractRule {
	protected $container;

	public function setContainer($container) {
		$this->container = $container;
	}
	
	public function validate($input)
	{
		if (!isset($this->container)) {
			throw new ApplicationException('Container not found!');
		}
	}
}
