<?php

namespace Plasticode\Collections\Generic;

use Webmozart\Assert\Assert;

abstract class TypedCollection extends Collection
{
    protected string $class = '';

    protected function __construct(?array $data)
    {
        Assert::notEmpty($this->class);

        if ($data) {
            Assert::allIsInstanceOf($data, $this->class);
        }

        parent::__construct($data);
    }
}
