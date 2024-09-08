<?php

namespace Plasticode\Collections\Generic;

use Webmozart\Assert\Assert;

/**
 * {@see ScalarCollection} storing strings.
 */
class StringCollection extends ScalarCollection
{
    protected function __construct(?array $data)
    {
        if ($data) {
            Assert::allString($data);
        }

        parent::__construct($data);
    }
}
