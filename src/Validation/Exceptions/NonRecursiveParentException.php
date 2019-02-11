<?php

namespace Plasticode\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class NonRecursiveParentException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Parent entity creates recursion.'
		]
	];
}
