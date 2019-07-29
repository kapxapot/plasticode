<?php

namespace Plasticode;

use Plasticode\Exceptions\InvalidArgumentException;
use Plasticode\Util\Strings;

class Query
{
    /**
     * ORM query
     *
     * @var \ORM
     */
    private $query;
    
    /**
     * Method for model creation
     *
     * @var \Closure
     */
    private $createModel;

    /**
     * Method for finding a model
     *
     * @var \Closure
     */
    private $find;
    
    public function __construct(\ORM $query = null, \Closure $createModel = null, \Closure $find = null)
    {
        if (is_null($query)) {
            return;
        }

        $this->query = $query;
        
        if (is_null($createModel)) {
            throw new InvalidArgumentException(
                'Query requires createModel function!'
            );
        }
    
        $this->createModel = $createModel;

        if (is_null($find)) {
            throw new InvalidArgumentException(
                'Query requires find function!'
            );
        }
    
        $this->find = $find;
    }

    /**
     * Get underlying \ORM query
     *
     * @return \ORM|null
     */
    public function getOrmQuery() : ?\ORM
    {
        return $this->query;
    }
    
    public static function empty() : self
    {
        return new static();
    }
    
    public function isEmpty() : bool
    {
        return $this->query === null;
    }
    
    // renderers
    
    public function all() : Collection
    {
        if ($this->isEmpty()) {
            return Collection::makeEmpty();
        }
        
        $objs = $this->query->findMany();
        
        $all = array_map(function ($obj) {
            $func = $this->createModel;
            return $func($obj);
        }, $objs ?? []);
        
        return Collection::make($all);
    }
    
    public function find($id)
    {
        $func = $this->find;
        
        return $func($this, $id)->one();
    }
    
    public function one()
    {
        if ($this->isEmpty()) {
            return null;
        }
        
        $obj = $this->query->findOne();
        $func = $this->createModel;
        
        return $func($obj);
    }

    public function random()
    {
        $count = $this->count();
        
        if ($count === 0) {
            return null;
        }
        
        $offset = rand(0, $count - 1);
        
        return $this->slice($offset, 1)->one();
    }
    
    public function count() : int
    {
        if ($this->isEmpty()) {
            return 0;
        }
        
        return $this->query->count();
    }
    
    public function any() : bool
    {
        return $this->count() > 0;
    }
    
    public function delete()
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->query->deleteMany();
    }
    
    // mmm
    
    private function branch(\Closure $queryModifier) : self
    {
        if ($this->isEmpty()) {
            return $this;
        }

        $query = $queryModifier($this->query);
        
        return new Query($query, $this->createModel, $this->find);
    }
    
    // idiorm funcs
    
    public function __call($name, array $args) : self
    {
        return $this->branch(
            function ($q) use ($name, $args) {
                return $q->{$name}(...$args);
            }
        );
    }

    public function whereIn($field, $values) : self
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }
        
        if (!is_array($values)) {
            throw new InvalidArgumentException(
                'WhereIn error: values must be a Collection or an array.'
            );
        }
        
        return $this->branch(function ($q) use ($field, $values) {
            return $q->whereIn($field, $values);
        });
    }

    public function whereNotIn($field, $values) : self
    {
        if ($values instanceof Collection) {
            $values = $values->toArray();
        }
        
        if (!is_array($values)) {
            throw new InvalidArgumentException(
                'WhereNotIn error: values must be a Collection or an array.'
            );
        }
        
        return $this->branch(
            function ($q) use ($field, $values) {
                return $q->whereNotIn($field, $values);
            });
    }

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
    
    public function slice(int $offset, int $limit) : self
    {
        return $this->offset($offset)->limit($limit);
    }

    // extensions
    
    public function search(string $searchQuery, string $where, int $paramCount = 1) : self
    {
        return $this->branch(
            function ($q) use ($searchQuery, $where, $paramCount) {
                $words = Strings::toWords($searchQuery);
                
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
