<?php

namespace Plasticode\Events;

use Plasticode\Models\DbModel;

abstract class EntityEvent extends Event
{
    public abstract function getEntity() : DbModel;

    /**
     * Get associated entity id.
     */
    public function getEntityId() : ?int
    {
        $entity = $this->getEntity();

        return $entity
            ? $entity->getId()
            : null;
    }

    public function equals(?Event $event) : bool
    {
        return $event
            && $this->getClass() == $event->getClass()
            && $event instanceof self
            && $this->getEntityId() === $event->getEntityId();
    }

    public function __toString() : string
    {
        $str = parent::__toString();
        $entity = $this->getEntity();

        if ($entity) {
            $str .= ' (' . $entity . ')';
        }

        return $str;
    }
}
