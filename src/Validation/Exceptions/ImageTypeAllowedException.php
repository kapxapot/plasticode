<?php

namespace Plasticode\Validation\Exceptions;

use Respect\Validation\Exceptions\ValidationException;

class ImageTypeAllowedException extends ValidationException {
	public static $defaultTemplates = [
		self::MODE_DEFAULT => [
			self::STANDARD => 'Incorrect image type.'
		]
	];
}
