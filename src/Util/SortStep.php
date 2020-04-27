<?php

namespace Plasticode\Util;

use Plasticode\Traits\PropertyAccess;
use Webmozart\Assert\Assert;

class SortStep
{
    use PropertyAccess;

    private ?string $field = null;

    /** @var callable|null */
    private $by = null;

    /**
     * Is sort step descending
     */
    private bool $desc;

    /**
     * Sort logic type
     */
    private string $type;

    /**
     * @param string|null $field If sorting by field
     * @param callable|null $by If sorting by callable
     * @param boolean $desc Set true if descending
     * @param string|null $type Sort::STRING, Sort::NULL, Sort::BOOL, Sort::DATE. null = Sort::NUMBER (default)
     */
    public function __construct(
        ?string $field,
        ?callable $by = null,
        bool $desc = false,
        ?string $type = null
    )
    {
        Assert::true(
            strlen($field) > 0 || !is_null($by),
            'Either $field (string) or $by (callable) must be provided.'
        );

        $this->field = $field;
        $this->by = $by;
        $this->desc = $desc;
        $this->type = $type ?? Sort::NUMBER;
    }

    public static function asc(string $field, ?string $type = null) : self
    {
        return self::byField($field, $type);
    }

    public static function desc(string $field, ?string $type = null) : self
    {
        return self::byFieldDesc($field, $type);
    }

    /**
     * Creates sort step by field with ASC ordering.
     */
    public static function byField(string $field, ?string $type = null) : self
    {
        return new static($field, null, false, $type);
    }

    /**
     * Creates sort step by field with DESC ordering.
     */
    public static function byFieldDesc(string $field, ?string $type = null) : self
    {
        return new static($field, null, true, $type);
    }

    public static function byFunc(
        callable $by,
        ?string $type = null
    ) : self
    {
        return new static(null, $by, false, $type);
    }

    public static function byFuncDesc(
        callable $by,
        ?string $type = null
    ) : self
    {
        return new static(null, $by, true, $type);
    }

    public function getField() : ?string
    {
        return $this->field;
    }

    public function hasField() : bool
    {
        return strlen($this->field) > 0;
    }

    public function getBy() : ?callable
    {
        return $this->by;
    }

    public function hasBy() : bool
    {
        return !is_null($this->by);
    }

    /**
     * Returns object value by field or callable (depends on step settings).
     *
     * @param mixed $obj
     * @return mixed
     */
    public function getValue($obj)
    {
        if ($this->hasBy()) {
            $by = $this->by;
            return $by($obj);
        }

        return self::getProperty($obj, $this->field);
    }

    /**
     * True = DESC, false = ASC.
     */
    public function isDesc() : bool
    {
        return $this->desc;
    }

    public function getType() : string
    {
        return $this->type;
    }
}
