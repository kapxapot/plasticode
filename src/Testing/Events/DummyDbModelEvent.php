<?php

namespace Plasticode\Testing\Events;

use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;
use Plasticode\Testing\Dummies\DummyDbModel;

class DummyDbModelEvent extends EntityEvent
{
    private DummyDbModel $dummyModel;

    public function __construct(DummyDbModel $dummyModel, ?Event $parent = null)
    {
        parent::__construct($parent);

        $this->dummyModel = $dummyModel;
    }

    public function getEntity() : DummyDbModel
    {
        return $this->getDummyModel();
    }

    public function getDummyModel() : DummyDbModel
    {
        return $this->dummyModel;
    }
}
