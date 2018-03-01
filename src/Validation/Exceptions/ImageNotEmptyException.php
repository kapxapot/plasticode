<?php

namespace Plasticode\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ImageNotEmptyException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'No image.'
		]
	];
}
