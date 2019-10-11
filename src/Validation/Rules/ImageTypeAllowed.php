<?php

namespace Plasticode\Validation\Rules;

use Plasticode\IO\Image;
use Respect\Validation\Rules\AbstractRule;

class ImageTypeAllowed extends AbstractRule
{
    public function validate($input)
    {
        $image = Image::parseBase64($input);

        return $image->isValid();
    }
}
