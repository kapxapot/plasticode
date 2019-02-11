<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Util\Pluralizer;
use Plasticode\Util\Strings;

abstract class DbModel extends SerializableModel
{
    protected static $table;
    
    protected static $idField = 'id';

    protected static $sortOrder = [];

    protected static $sortField = null;
    protected static $sortReverse = false;

    protected static $tagsField = 'tags';
    
    protected $obj;
    
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
    
    public static function getTable()
    {
        if (strlen(static::$table) > 0) {
            return static::$table;
        }

        $class = Strings::lastChunk(static::class, '\\');
        $plural = Pluralizer::plural($class);
        $table = Strings::toSnakeCase($plural);

        return $table;
    }
    
    /**
     * Use getId() instead of id when $idField is custom.
     * It is recommended to use getId() always for safer code.
     */
    public function getId()
    {
	    $idField = static::$idField;
	    return $this->{$idField};
    }

    /**
     * Creates "empty" model. Alias for empty constructor.
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Creates model based on database object. If object is null, returns null.
     * A kind of wrapper for database object.
     */
	protected static function make($obj)
    {
        if (!$obj) {
            return null;
        }
        
        $model = new static($obj);
        
        $model->afterMake();
        
        return $model;
    }
    
    /**
     * Transforms an array of database objects into a collection of models.
     */
    public static function makeMany($objs)
    {
	    $many = array_map(function ($obj) {
	        return static::make($obj);
	    }, $objs ?? []);
	    
	    return Collection::make($many);
    }

	public static function getMany($where = null)
	{
	    $objs = self::$db->getManyObj(self::getTable(), $where);
	    return self::makeMany($objs);
	}
    
    public static function getManyByField($field, $value, $where = null)
    {
        $objs = self::$db->getManyObjByField(self::getTable(), $field, $value, $where);
        return self::makeMany($objs);
    }

    public static function get($id, $where = null)
    {
        return self::getByField(static::$idField, $id, $where);
    }
    
    public static function getBy($where)
    {
        $obj = self::$db->getObjBy(self::getTable(), $where);
        return self::make($obj);
    }
    
    public static function getByField($field, $value, $where = null)
    {
        $obj = self::$db->getObjByField(self::getTable(), $field, $value, $where);
        return self::make($obj);
    }
    
	public static function getRaw($query, $params)
	{
		return self::$db->getMany(self::getTable(), function($q) use ($query, $params) {
			return $q->rawQuery($query, $params);
		});
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
    
    private static function applySortOrder($query)
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
	
	public static function getAll($where = null)
	{
		return self::getMany(function ($q) use ($where) {
		    $q = self::applySortOrder($q);

		    if ($where) {
		        $q = $where($q);
		    }
		    
		    return $q;
		});
	}
	
	public static function getCount($where = null)
	{
	    return self::$db->getCount(self::getTable(), $where);
	}
	
	protected static function where($field, $value, $where = null)
	{
	    return function ($q) use ($field, $value, $where) {
            $q = $q->where($field, $value);
            
            if ($where) {
                $q = $where($q);
            }
            
            return $q;
    	};
	}
	
	public static function getAllByField($field, $value, $where = null)
	{
	    return self::getAll(
	        self::where($field, $value, $where)
	    );
	}
	
    public static function deleteBy($where)
    {
        self::$db->deleteBy(self::getTable(), $where);
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
	    $this->beforeSave();
	    $this->obj->save();
	}

    protected function setFieldNoStamps($field, $value)
	{
		self::$db->setFieldNoStamps(self::getTable(), $this->getId(), $field, $value);
	}
	
	public function serialize()
	{
	    return $this->obj
	        ? $this->obj->asArray()
	        : null;
	}
	
	// events
	// they transform database object and return it
	
	protected function afterMake()
	{
	}
	
	protected function beforeSave()
	{
	}
	
	// magic
	
	public function __toString()
	{
	    $class = static::class;
	    $id = $this->getId();
	    
	    return "[{$id}] {$class}";
	}
}
