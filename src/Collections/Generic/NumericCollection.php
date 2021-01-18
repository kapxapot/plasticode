<?php

namespace Plasticode\Collections\Generic;

use Webmozart\Assert\Assert;

/**
 * {@see ScalarCollection}, storing numeric values.
 * 
 * Also, provides aggregate operations such as max() & sum().
 */
class NumericCollection extends ScalarCollection
{
    protected function __construct(?array $data)
    {
        if ($data) {
            Assert::allNumeric($data);
        }

        parent::__construct($data);
    }

    /**
     * Returns max value.
     * 
     * In case of empty collection returns null.
     *
     * @return mixed
     */
    public function max()
    {
        return $this->isEmpty()
            ? null
            : max($this->data);
    }

    /**
     * Returns sum of the elements.
     * 
     * In case of empty collection returns 0.
     *
     * @return mixed
     */
    public function sum()
    {
        return array_sum($this->data);
    }
}
