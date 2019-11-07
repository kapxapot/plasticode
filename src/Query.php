<?php

namespace Plasticode;

use Plasticode\Models\DbModel;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

class Query
{
    /**
     * ORM query
     *
     * @var \ORM
     */
    private $query;

    /**
     * Id field name
     *
     * @var string
     */
    private $idField;
    
    /**
     * Method for conversion of dbObj to model
     *
     * @var \Closure
     */
    private $toModel;

    /**
     * Empty query
     *
     * @var self
     */
    private static $empty;
    
    /**
     * Constructor.
     *
     * @param \ORM $query The base query. Can be null for an empty query
     * @param string $idField Must be provided for non-empty query
     * @param \Closure $toModel Must be provided for non-empty query
     */
    public function __construct(\ORM $query = null, string $idField = null, \Closure $toModel = null)
    {
        if (is_null($query)) {
            return;
        }

        $this->query = $query;

        Assert::notNull(
            $idField,
            'Non-empty query requires $idField!'
        );
        
        Assert::notNull(
            $toModel,
            'Non-empty query requires toModel() function!'
        );
    
        $this->idField = $idField;
        $this->toModel = $toModel;
    }

    /**
     * Get underlying \ORM query.
     *
     * @return \ORM|null
     */
    public function getOrmQuery() : ?\ORM
    {
        return $this->query;
    }
    
    /**
     * Returns "empty" query (without table and filters).
     *
     * @return self
     */
    public static function empty() : self
    {
        if (is_null(self::$empty)) {
            self::$empty = new static();
        }

        return self::$empty;
    }
    
    /**
     * Checks if the query is empty (without table and filters).
     *
     * @return boolean
     */
    public function isEmpty() : bool
    {
        return $this->query === null;
    }
    
    /**
     * Executes query and returns all records.
     * 
     * "Select all".
     * In case of empty Query returns empty collection.
     *
     * @return Collection
     */
    public function all() : Collection
    {
        if ($this->isEmpty()) {
            return Collection::makeEmpty();
        }
        
        $objs = $this->query->findMany();
        
        $all = array_map(
            function ($obj) {
                return ($this->toModel)($obj);
            },
            $objs ?? []
        );
        
        return Collection::make($all);
    }
    
    /**
     * Looks for a record with provided id.
     *
     * @param string|int $id
     * @return null|\Plasticode\Models\DbModel
     */
    public function find($id) : ?DbModel
    {
        return $this
            ->where($this->idField, $id)
            ->one();
    }
    
    /**
     * Executes query and returns the first record if any.
     * 
     * "Select one".
     * In case of empty query returns null.
     *
     * @return null|\Plasticode\Models\DbModel
     */
    public function one() : ?DbModel
    {
        if ($this->isEmpty()) {
            return null;
        }
        
        $obj = $this->query->findOne();
        
        return ($this->toModel)($obj);
    }

    /**
     * Executes query and returns a random record.
     *
     * In case of empty query (or no records) returns null.
     * 
     * @return null|\Plasticode\Models\DbModel
     */
    public function random() : ?DbModel
    {
        $count = $this->count();
        
        if ($count === 0) {
            return null;
        }
        
        $offset = rand(0, $count - 1);
        
        return $this
            ->slice($offset, 1)
            ->one();
    }
    
    /**
     * Executes query and returns record count.
     * 
     * "Select count(*)".
     * In case of empty query returns 0.
     *
     * @return integer
     */
    public function count() : int
    {
        if ($this->isEmpty()) {
            return 0;
        }
        
        return $this->query->count();
    }
    
    /**
     * Executes query and checks if there are any records.
     *
     * @return bool
     */
    public function any() : bool
    {
        return $this->count() > 0;
    }
    
    /**
     * Deletes records based on the query.
     * 
     * "Delete all".
     * In case of empty query returns null.
     *
     * @return null|bool
     */
    public function delete() : ?bool
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->query->deleteMany();
    }
    
    /**
     * Creates new Query based on the current one
     * plus applied modification.
     *
     * @param \Closure $queryModifier
     * @return mixed
     */
    private function branch(\Closure $queryModifier)
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $result = $queryModifier($this->query);

        // if query resulted in any final result (!= query)
        // return it as is
        if (!($result instanceof \ORM)) {
            return $result;
        }
        
        // if query modification resulted in another query
        // wrap it and return as a new Query
        return new Query(
            $result, $this->idField, $this->toModel
        );
    }
    
    /**
     * Delegates method call to the underlying \ORM query.
     *
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->branch(
            function ($q) use ($name, $args) {
                return $q->{$name}(...$args);
            }
        );
    }

    /**
     * Wrapper method for the underlying whereIn().
     * 
     * Allows passing an array or a Collection.
     *
     * @param string $field
     * @param array|\Plasticode\Collection $values Array or Collection
     * @return self
     */
    public function whereIn(string $field, $values) : self
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }
        
        Assert::isArray(
            $values,
            'WhereIn error: values must be a Collection or an array.'
        );
        
        return $this->branch(
            function ($q) use ($field, $values) {
                return $q->whereIn($field, $values);
            }
        );
    }

    /**
     * Wrapper method for the underlying whereNotIn().
     *
     * Allows passing an array or a Collection.
     * 
     * @param string $field
     * @param array|\Plasticode\Collection $values Array or Collection
     * @return self
     */
    public function whereNotIn(string $field, $values) : self
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }
        
        Assert::isArray(
            $values,
            'WhereNotIn error: values must be a Collection or an array.'
        );
        
        return $this->branch(
            function ($q) use ($field, $values) {
                return $q->whereNotIn($field, $values);
            }
        );
    }

    /**
     * Wrapper method for the underlying offset().
     * 
     * Applies only if $offset > 0.
     *
     * @param integer $offset
     * @return self
     */
    public function offset(int $offset) : self
    {
        if ($offset <= 0) {
            return $this;
        }
        
        return $this->branch(
            function ($q) use ($offset) {
                return $q->offset($offset);
            }
        );
    }
    
    /**
     * Wrapper method for the underlying limit().
     * 
     * Applies only if $limit > 0.
     *
     * @param integer $limit
     * @return self
     */
    public function limit(int $limit) : self
    {
        if ($limit <= 0) {
            return $this;
        }
        
        return $this->branch(
            function ($q) use ($limit) {
                return $q->limit($limit);
            }
        );
    }
    
    /**
     * Gets chunk based on offset and limit.
     * 
     * Shortcut for offset() + limit().
     *
     * @param integer $offset
     * @param integer $limit
     * @return self
     */
    public function slice(int $offset, int $limit) : self
    {
        return $this
            ->offset($offset)
            ->limit($limit);
    }

    /**
     * Breaks the search string into words
     * and applies where() with each of them
     * using AND.
     *
     * @param string $searchStr One or several words
     * @param string $where
     * @param integer $paramCount How many times every word must be passed to where()
     * @return self
     */
    public function search(string $searchStr, string $where, int $paramCount = 1) : self
    {
        return $this->branch(
            function ($q) use ($searchStr, $where, $paramCount) {
                $words = Strings::toWords($searchStr);
                
                foreach ($words as $word) {
                    $wrapped = '%' . $word . '%';
                    $params = array_fill(0, $paramCount, $wrapped);
                    $q = $q->whereRaw($where, $params);
                }
        
                return $q;
            }
        );
    }
}
