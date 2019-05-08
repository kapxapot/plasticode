<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Query;
use Plasticode\Exceptions\ApplicationException;
use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\Util\Pluralizer;
use Plasticode\Util\Strings;

abstract class DbModel extends Model implements SerializableInterface
{
    protected static $table;
    
    protected static $idField = 'id';

    protected static $sortOrder = [];

    protected static $sortField = null;
    protected static $sortReverse = false;

    protected static $tagsField = 'tags';

    /**
     * Wraps an existing database object or creates a new one using provided data.
     * If data is null, wraps an empty database object.
     */
    public function __construct($obj = null)
    {
        parent::__construct();
        
        if ($obj == null || is_array($obj)) {
            $this->obj = self::$db->create(self::getTable(), $obj);
        } else {
            $this->obj = $obj;
        }
    }
    
    /**
     * Static alias for new().
     */
    public static function create($obj = null)
    {
        return new static($obj);
    }
    
    /**
     * create() + save().
     */
    public static function store($obj = null)
    {
        $model = static::create($obj);
        return $model->save();
    }
    
    private static function pluralClass()
    {
        $class = Strings::lastChunk(static::class, '\\');
        return Pluralizer::plural($class);
    }
    
    public static function getTable()
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
     * 
     * Use this query if you need any sort order different from default.
     */
    public static function baseQuery() : Query
    {
        $dbQuery = self::$db->forTable(self::getTable());
        
        $createModel = function ($obj) {
            return self::fromDbObj($obj);
        };
        
        $find = function (Query $query, $id) {
            return $query->where(static::$idField, $id);
        };
        
        return new Query($dbQuery, $createModel, $find);
    }
    
    /**
     * Base query with applied sort order.
     */
    public static function query() : Query
    {
        return self::applySortOrder(self::baseQuery());
    }
    
    /**
     * Returns entity generator for this model.
     */
    public static function getGenerator()
    {
        $plural = self::pluralClass();
        $gen = self::$container->generatorResolver->resolveEntity($plural);

        return $gen;
    }
    
    /**
     * Returns validation rules for this model.
     */
    public static function getRules($data)
    {
        $gen = self::getGenerator();
		$rules = $gen->getRules($data);
		
		return $rules;
    }
    
    /**
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
    public function isPersisted()
    {
        return $this->hasId();
    }
    
    public function failIfNotPersisted()
    {
        if (!$this->isPersisted()) {
            throw new ApplicationException('Object must be persisted.');
        }
    }

    /**
     * Wrapper for model creation.
     * 
     * Checks if obj is null and doesn't create model for null.
     */
    private static function fromDbObj($obj)
    {
        if (!$obj) {
            return null;
        }
        
        return static::create($obj);
    }
	
	/**
	 * Shortcut for getting all models with sort applied.
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
    public static function get($id, bool $ignoreCache = false)
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

    private static function buildSortOrder()
    {
        $order = static::$sortOrder;
        
        if (empty($order) && strlen(static::$sortField) > 0) {
            $order[] = [
                'field' => static::$sortField,
                'reverse' => static::$sortReverse,
            ];
        }
        
        return $order;
    }
    
    private static function applySortOrder(Query $query) : Query
    {
	    $sortOrder = self::buildSortOrder();
	    
	    foreach ($sortOrder as $sort) {
	        $field = $sort['field'];
	        
	        $query = ($sort['reverse'] ?? false)
	            ? $query->orderByDesc($field)
	            : $query->orderByAsc($field);
	    }
	    
	    return $query;
    }

	// rights
	
	public static function tableAccess()
	{
	    return self::$db->getRights(self::getTable());
	}
	
	public static function can($rights)
	{
	    return self::$db->can(self::getTable(), $rights);
	}
	
	public function access()
	{
	    return self::$db->getRights(self::getTable(), $this->obj);
	}
	
	// instance methods
	
	public function save()
	{
	    $this->obj->save();
	    
	    return $this;
	}

	public function serialize()
	{
	    return $this->obj
	        ? $this->obj->asArray()
	        : null;
	}
	
	public function entityAlias()
	{
	    return self::getTable();
	}

	public function toString()
	{
	    $class = static::class;
	    $id = $this->getId();
	    
	    return "[{$id}] {$class}";
	}
}
