<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Data\Rights;
use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\Util\Classes;
use Plasticode\Util\Pluralizer;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;
use Plasticode\Generators\EntityGenerator;
use Webmozart\Assert\Assert;

abstract class DbModel extends Model implements SerializableInterface
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $table;
    
    /**
     * Id field name
     *
     * @var string
     */
    protected static $idField = 'id';

    /**
     * Tags field name
     *
     * @var string
     */
    protected static $tagsField = 'tags';

    /**
     * Default sort field name
     *
     * @var string
     */
    protected static $sortField = null;

    /**
     * Default sort direction
     *
     * @var boolean
     */
    protected static $sortReverse = false;

    /**
     * Static alias for new().
     * 
     * @param array|\ORM $obj
     * @return self
     */
    public static function create($obj = null) : self
    {
        return new static($obj);
    }

    /**
     * Wrapper for model creation.
     * 
     * @param \ORM $obj
     * @return self
     */
    private static function fromDbObj(\ORM $obj) : self
    {
        return static::create($obj);
    }
    
    /**
     * Shortcut for create() + save().
     * 
     * @param array|\ORM $obj
     * @return self
     */
    public static function store($obj = null) : self
    {
        $model = static::create($obj);
        return self::save($model);
    }
    
    private static function pluralClass() : string
    {
        $class = Classes::shortName(static::class);
        return Pluralizer::plural($class);
    }
    
    public static function getTable() : string
    {
        if (strlen(static::$table) > 0) {
            return static::$table;
        }

        $plural = self::pluralClass();
        $table = Strings::toSnakeCase($plural);

        return $table;
    }
    
    /**
     * Bare query without sort.
     */
    private static function baseQuery() : Query
    {
        $dbQuery = self::$db->forTable(self::getTable());
        
        $toModel = function ($obj) {
            return self::fromDbObj($obj);
        };
        
        return new Query($dbQuery, static::$idField, $toModel);
    }
    
    /**
     * Query with default sort.
     */
    public static function query() : Query
    {
        $query = self::baseQuery();
        $sortOrder = static::getSortOrder();

        return !empty($sortOrder)
            ? $query->withSort($sortOrder)
            : $query;
    }

    /**
     * Returns sort order.
     *
     * @return \Plasticode\Util\SortStep[]
     */
    protected static function getSortOrder() : array
    {
        if (strlen(static::$sortField) == 0) {
            return [];
        }

        return [
            new SortStep(static::$sortField, static::$sortReverse)
        ];
    }
    
    /**
     * Returns entity generator for this model.
     */
    public static function getGenerator() : EntityGenerator
    {
        $plural = self::pluralClass();
        $gen = self::$container->generatorResolver->resolveEntity($plural);

        return $gen;
    }
    
    /**
     * Returns validation rules for this model.
     */
    public static function getRules($data) : array
    {
        $gen = self::getGenerator();
        $rules = $gen->getRules($data);
        
        return $rules;
    }
    
    /**
     * Returns the id of the model.
     * 
     * Use getId() instead of id when $idField is custom.
     * It is recommended to use getId() always for safer code.
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
    
    /**
     * Shortcut for getting all models.
     */
    public static function getAll() : Collection
    {
        return self::query()->all();
    }
    
    public static function getCount() : int
    {
        return self::baseQuery()->count();
    }
    
    /**
     * Shortcut for getting model by id.
     */
    public static function get($id, bool $ignoreCache = false) : ?self
    {
        $name = static::class . $id;
        
        return self::staticLazy(
            function () use ($id) {
                return self::baseQuery()->find($id);
            },
            $name,
            $ignoreCache
        );
    }

    private static function tableRights() : Rights
    {
        return self::$db->getTableRights(
            self::getTable()
        );
    }
    
    public static function tableAccess() : array
    {
        return self::tableRights()->forTable();
    }
    
    public static function can($rights) : bool
    {
        return self::tableRights()->can($rights);
    }
    
    public function access() : array
    {
        return self::tableRights()->forEntity($this->obj->asArray());
    }
    
    public static function save(self $model) : self
    {
        $obj = $model->getObj();

        $ormObj = $obj instanceof \ORM
            ? $obj
            : self::$db->create(self::getTable(), $obj);
            
        $ormObj->save();

        return static::create($ormObj);
    }

    public function serialize() : array
    {
        return $this->toArray();
    }
    
    public function entityAlias() : string
    {
        return self::getTable();
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
