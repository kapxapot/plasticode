<?php

namespace Plasticode\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use Plasticode\IO\Image;

class ImageTypeAllowed extends AbstractRule
{
	public function validate($input)
	{
		$image = Image::parseBase64($input);

		return $image->isValid();
	}
}
