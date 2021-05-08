<?php

namespace Plasticode\Collections\Generic;

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

    /**
     * Checks if the collection contains the provided value.
     * 
     * @param mixed $value
     */
    public function contains($value): bool
    {
        return in_array($value, $this->data);
    }

    /**
     * Returns distinct values.
     * 
     * @return static
     */
    public function distinct(): self
    {
        return parent::distinctBy(
            fn ($v) => $v
        );
    }
}
