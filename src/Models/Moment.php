<?php

namespace Plasticode\Models;

use Plasticode\Util\Date;

class Moment
{
    private string $date;

    public function __construct(string $date)
    {
        $this->date = $date;
    }

    public function iso(): string
    {
        return Date::iso($this->date);
    }

    public function hasTime(): bool
    {
        return Date::hasTime($this->date);
    }

    public function __toString()
    {
        return $this->date;
    }
}
