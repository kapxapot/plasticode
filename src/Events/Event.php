<?php

namespace Plasticode\Events;

use Plasticode\Models\DbModel;
use Plasticode\Traits\GetClass;

abstract class Event
{
    use GetClass;

    /**
     * Parent event
     */
    private ?self $parent;

    public function __construct(?self $parent = null)
    {
        $this->setParent($parent);
    }

    public function getParent() : ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent) : void
    {
        $this->parent = $parent;
    }

    public function withParent(self $parent) : self
    {
        $this->setParent($parent);
        return $this;
    }

    public function hasParent() : bool
    {
        return $this->parent !== null;
    }

    public abstract function getEntity() : DbModel;

    /**
     * Get associated entity id.
     *
     * @return mixed
     */
    public function getEntityId()
    {
        $entity = $this->getEntity();

        return $entity !== null
            ? $entity->getId()
            : null;
    }

    public function __toString() : string
    {
        $str = $this->getClass();
        $entity = $this->getEntity();

        if ($entity) {
            $str .= ' (' . $entity . ')';
        }

        return $str;
    }
}
