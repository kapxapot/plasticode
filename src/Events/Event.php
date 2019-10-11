<?php

namespace Plasticode\Events;

use Plasticode\Models\DbModel;

abstract class Event
{
    /**
     * Parent event
     *
     * @var Event
     */
    private $parent;

    public function __construct(self $parent = null)
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
        return !is_null($this->parent);
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

        return is_null($entity)
            ? null
            : $entity->getId();
    }

    public function getClass() : string
    {
        return static::class;
    }

    public function toString() : string
    {
        $str = $this->getClass();
        $entity = $this->getEntity();

        if (!is_null($entity)) {
            $str .= " (" . $entity->toString() . ")";
        }

        return  $str;
    }
}
