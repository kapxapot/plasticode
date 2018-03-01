<?php

namespace Plasticode\Validation\Rules;

use Respect\Validation\Rules\AbstractRule;

use Plasticode\Gallery\Gallery;
use Plasticode\IO\Image;

class ImageTypeAllowed extends AbstractRule {
	public function validate($input) {
		$image = Image::parseBase64($input);

		return Gallery::getExtension($image->imgType) !== null;
	}
}
