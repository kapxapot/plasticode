<?php

namespace Plasticode;

use Webmozart\Assert\Assert;

class ScalarCollection extends Collection
{
    protected function __construct(?array $data)
    {
        if ($data) {
            Assert::allScalar($data);
        }

        parent::__construct($data);
    }

    public function contains($value) : bool
    {
        return in_array($value, $this->data);
    }
}
