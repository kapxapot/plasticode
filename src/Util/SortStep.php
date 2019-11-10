<?php

namespace Plasticode\Util;

class SortStep
{
    /** @var string */
    private $field;

    /**
     * Is sort step descending.
     *
     * @var bool
     */
    private $desc;

    /**
     * @param string $field
     * @param boolean $desc
     */
    public function __construct(string $field, bool $desc = false)
    {
        $this->field = $field;
        $this->desc = $desc;
    }

    /**
     * Creates sort step with ASC ordering.
     *
     * @param string $field
     * @return self
     */
    public static function create(string $field) : self
    {
        return new static($field);
    }

    /**
     * Creates sort step with DESC ordering.
     *
     * @param string $field
     * @return self
     */
    public static function createDesc(string $field) : self
    {
        return new static($field, true);
    }

    public function getField() : string
    {
        return $this->field;
    }

    /**
     * True = DESC, false = ASC.
     *
     * @return boolean
     */
    public function isDesc() : bool
    {
        return $this->desc;
    }
}
