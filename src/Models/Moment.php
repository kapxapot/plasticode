<?php

namespace Plasticode\Models;

use Plasticode\Util\Date;

class Moment extends Model
{
    public function __construct($date)
    {
        parent::__construct();
        
        $this->date = $date;
    }
    
    // PROPS
    
    public function iso()
    {
        return Date::iso($this->date);
    }
    
    public function hasTime()
    {
        return Date::hasTime($this->date);
    }
    
    // FUNCS
    
    public function toString()
    {
        return $this->date;
    }
}
