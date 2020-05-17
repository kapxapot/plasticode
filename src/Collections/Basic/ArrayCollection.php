<?php

namespace Plasticode\Collections\Basic;

use Webmozart\Assert\Assert;

class ArrayCollection extends Collection
{
    protected function __construct(?array $data)
    {
        if ($data) {
            Assert::allIsArray($data);
        }

        parent::__construct($data);
    }
}
