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

    public function any($selector = null, $value = null): bool
    {
        // anyFirst() can be used since typed collection doesn't contain nulls
        return $this->anyFirst($selector, $value);
    }
}
