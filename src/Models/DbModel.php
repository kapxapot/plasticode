<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\SerializableInterface;
use Webmozart\Assert\Assert;

abstract class DbModel extends Model implements SerializableInterface
{
    protected static $idField = 'id';

    /**
     * Static alias for new().
     * 
     * @param array|\ORM|null $obj
     * @return self
     */
    public static function create($obj = null) : self
    {
        return new static($obj);
    }

    /**
     * Returns the id of the model.
     * 
     * Use getId() instead of id when $idField is custom.
     * It is recommended to use getId() always for safer code.
     * 
     * @return integer|string|null
     */
    public function getId()
    {
        $idField = static::$idField;
        $id = $this->{$idField};
        
        return is_numeric($id)
            ? intval($id)
            : $id;
    }
    
    public function hasId() : bool
    {
        $id = $this->getId();
        
        return is_numeric($id)
            ? $id > 0
            : strlen($id) > 0;
    }
    
    /**
     * Was model saved or not.
     */
    public function isPersisted() : bool
    {
        return $this->hasId();
    }
    
    public function failIfNotPersisted() : void
    {
        Assert::true(
            $this->isPersisted(),
            'Object must be persisted.'
        );
    }

    public function serialize() : array
    {
        return $this->toArray();
    }

    /**
     * Checks if two objects are equal.
     * 
     * Equal means:
     *  - Same class.
     *  - Same id.
     * 
     * @param self|null $model
     * @return boolean
     */
    public function equals(?self $model) : bool
    {
        return !is_null($model)
            && ($model->getId() === $this->getId())
            && (get_class($model) == static::class);
    }

    public function toString() : string
    {
        return '[' . $this->getId() . '] ' . static::class;
    }
}
