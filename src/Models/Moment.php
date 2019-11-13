<?php

namespace Plasticode\Models;

use Plasticode\Util\Date;

class Moment extends Model
{
    /** @var string */
    private $date;

    public function __construct(string $date)
    {
        parent::__construct();
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
    
    public function toString() : string
    {
        return $this->date;
    }
}
