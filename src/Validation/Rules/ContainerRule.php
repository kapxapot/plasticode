<?php

namespace Plasticode\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

abstract class ContainerRule extends AbstractRule {
	protected $container;

	public function setContainer($container) {
		$this->container = $container;
	}
}
