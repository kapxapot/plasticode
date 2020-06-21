<?php

namespace Plasticode\Testing\Events;

use Plasticode\Events\EntityEvent;
use Plasticode\Events\Event;
use Plasticode\Testing\Dummies\DbModelDummy;

class DbModelDummyEvent extends EntityEvent
{
    private DbModelDummy $dummyModel;

    public function __construct(DbModelDummy $dummyModel, ?Event $parent = null)
    {
        parent::__construct($parent);

        $this->dummyModel = $dummyModel;
    }

    public function getEntity() : DbModelDummy
    {
        return $this->getDummyModel();
    }

    public function getDummyModel() : DbModelDummy
    {
        return $this->dummyModel;
    }
}
