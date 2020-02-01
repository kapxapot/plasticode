<?php

namespace Plasticode\Tests;

class SortDummy
{
    /** @var integer */
    public $num;

    /** @var string */
    public $str;

    /** @var boolean */
    public $bool;

    /** @var string|null */
    public $null;

    /** @var string */
    public $date;

    public function __construct(int $num, string $str, bool $bool, ?string $null, string $date)
    {
        $this->num = $num;
        $this->str = $str;
        $this->bool = $bool;
        $this->null = $null;
        $this->date = $date;
    }
}
