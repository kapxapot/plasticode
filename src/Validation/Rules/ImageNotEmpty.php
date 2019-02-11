<?php

namespace Plasticode\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use Plasticode\IO\Image;

class ImageNotEmpty extends AbstractRule
{
	public function validate($input)
	{
		$image = Image::parseBase64($input);
		
		return $image->notEmpty();
	}
}
