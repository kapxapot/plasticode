<?php

namespace Plasticode;

use Plasticode\Util\Date;

class Moment
{
    /** @var string */
    private $date;

    public function __construct(string $date)
    {
        $this->date = $date;
    }
    
    public function iso() : string
    {
        return Date::iso($this->date);
    }
    
    public function hasTime() : bool
    {
        return Date::hasTime($this->date);
    }
    
    public function __toString() : string
    {
        return $this->date;
    }
}
