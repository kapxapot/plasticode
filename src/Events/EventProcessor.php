<?php

namespace Plasticode\Events;

use Plasticode\Contained;

abstract class EventProcessor extends Contained
{
    public function getClass() : string
    {
        return statis::class;
    }
}
