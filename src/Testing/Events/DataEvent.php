<?php

namespace Plasticode\Testing\Events;

use Plasticode\Events\Event;

class DataEvent extends Event
{
    private $data;

    public function __construct($data, ?Event $parent = null)
    {
        parent::__construct($parent);

        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
