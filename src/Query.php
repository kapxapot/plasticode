<?php

namespace Plasticode;

use Plasticode\Collections\Basic\DbModelCollection;
use Plasticode\Exceptions\SqlException;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Models\Basic\DbModel;
use Plasticode\Util\Arrays;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

/**
 * Idiorm wrapper integrated with DbModel.
 * 
 * @method self join(string $table, string|array $constraint, ?string $tableAlias = null)
 * @method self leftOuterJoin(string $table, string|array $constraint, ?string $tableAlias = null)
 * @method self rightOuterJoin(string $table, string|array $constraint, ?string $tableAlias = null)
 * @method self select(string $fields, ?string $alias = null)
 * @method self where(string $field, $value)
 * @method self whereAnyIs(array $conditions)
 * @method self whereLt(string $field, $value)
 * @method self whereGt(string $field, $value)
 * @method self whereNotNull(string $field)
 * @method self whereNotEqual(string $field, $value)
 * @method self whereNull(string $field)
 * @method self whereRaw(string $condition, array $params = null)
 */
class Query implements \IteratorAggregate, ArrayableInterface
{
    /**
     * Empty query.
     */
    private static ?self $empty = null;

    /**
     * ORM query.
     */
    private ?\ORM $query = null;

    /**
     * Id field name.
     */
    private string $idField;
    
    /**
     * Method for conversion of dbObj to model.
     */
    private \Closure $toModel;

    /**
     * Array of sort steps.
     *
     * @var SortStep[]
     */
    private array $sortOrder;
    
    /**
     * Constructor.
     *
     * @param \ORM $query The base query. Can be null for an empty query.
     * @param string $idField Must be provided for non-empty query.
     * @param \Closure $toModel Must be provided for non-empty query.
     * @param SortStep[] $sortOrder
     */
    public function __construct(
        \ORM $query = null,
        string $idField = null,
        \Closure $toModel = null,
        ?array $sortOrder = null
    )
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
        $this->sortOrder = $sortOrder ?? [];
    }

    /**
     * Get underlying \ORM query.
     */
    public function getOrmQuery() : ?\ORM
    {
        return $this->query;
    }

    /**
     * Returns "empty" query (without table and filters).
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
     */
    public function isEmpty() : bool
    {
        return is_null($this->query);
    }

    /**
     * Executes query and returns all records.
     * 
     * "Select all".
     * In case of empty Query returns empty collection.
     */
    public function all() : DbModelCollection
    {
        if ($this->isEmpty()) {
            return DbModelCollection::empty();
        }

        $query = $this->getSortedQuery();

        try {
            $objs = $query->findMany();

            $all = array_map(
                fn (\ORM $obj) => ($this->toModel)($obj),
                $objs ?? []
            );

            return DbModelCollection::make($all);
        } catch (\PDOException $pdoEx) {
            throw new SqlException(
                'Failed to execute query: ' . self::queryToString($query)
            );
        }
    }

    /**
     * Gets SQL statement for the ORM query.
     */
    private static function queryToString(\ORM $query) : string
    {
        $method = new \ReflectionMethod('\ORM', '_build_select');
        $method->setAccessible(true);

        $statement = $method->invoke($query);

        return $statement;
    }
    
    /**
     * Looks for a record with provided id.
     */
    public function find(?int $id) : ?DbModel
    {
        return $this
            ->filterById($id)
            ->one();
    }

    /**
     * Adds filter by id.
     */
    public function filterById(?int $id) : self
    {
        return $this
            ->where($this->idField, $id);
    }
    
    /**
     * Executes query and returns the first record if any.
     * 
     * "Select one".
     * In case of empty query returns null.
     */
    public function one() : ?DbModel
    {
        if ($this->isEmpty()) {
            return null;
        }
        
        $obj = $this->getSortedQuery()->findOne();
        
        return $obj
            ? ($this->toModel)($obj)
            : null;
    }

    /**
     * Returns query with applied sort order.
     */
    private function getSortedQuery() : \ORM
    {
        $query = $this->query;

        Assert::notNull(
            $query,
            'Cannot sort null query.'
        );

        foreach ($this->sortOrder as $sortStep) {
            $field = $sortStep->getField();

            $query = $sortStep->isDesc()
                ? $query->orderByDesc($field)
                : $query->orderByAsc($field);
        }

        return $query;
    }

    /**
     * Executes query and returns a random record.
     *
     * In case of empty query (or no records) returns null.
     * 
     * Note:
     * 
     * If the query results are changed during the execution
     * of this function, the function tries to get the first record.
     * 
     * This can be the case when:
     * 
     * - There were records on the counting step, but they disappered
     * before getting the random record.
     * 
     * - The count was 0 but before returning the empty record, some
     * records have appeared.
     */
    public function random() : ?DbModel
    {
        $count = $this->count();

        if ($count === 0) {
            return $this->one();
        }

        $offset = mt_rand(0, $count - 1);

        return $this->slice($offset, 1)->one()
            ?? $this->one();
    }

    /**
     * Executes query and returns record count.
     * 
     * "Select count(*)".
     * In case of empty query returns 0.
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
     * @param SortStep[]|null $sortOrder
     * @return mixed
     */
    private function branch(
        ?\Closure $queryModifier = null,
        ?array $sortOrder = null
    )
    {
        if ($this->isEmpty()) {
            return $this;
        }

        if (!is_null($queryModifier)) {
            $result = $queryModifier($this->query);

            // if query resulted in any final result (!= query)
            // return it as is
            if (!($result instanceof \ORM)) {
                return $result;
            }
        }
        
        // if query modification resulted in another query
        // wrap it and return as a new Query
        return new Query(
            $result ?? $this->query,
            $this->idField,
            $this->toModel,
            $sortOrder ?? $this->sortOrder
        );
    }
    
    /**
     * Delegates method call to the underlying \ORM query.
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->branch(
            fn ($q) => $q->{$name}(...$args)
        );
    }

    /**
     * Wrapper method for the underlying whereIn().
     * 
     * Allows passing an array or a ArrayableInterface.
     * In case of empty array returns empty query!
     *
     * @param array|ArrayableInterface $values
     */
    public function whereIn(string $field, $values) : self
    {
        $values = Arrays::adopt($values);

        if (empty($values)) {
            return self::empty();
        }

        return $this->branch(
            fn ($q) => $q->whereIn($field, $values)
        );
    }

    /**
     * Wrapper method for the underlying whereNotIn().
     *
     * Allows passing an array or a ArrayableInterface.
     * 
     * @param array|ArrayableInterface $values
     */
    public function whereNotIn(string $field, $values) : self
    {
        $values = Arrays::adopt($values);

        if (empty($values)) {
            return $this;
        }

        return $this->branch(
            fn ($q) => $q->whereNotIn($field, $values)
        );
    }

    /**
     * Wrapper method for the underlying offset().
     * 
     * Applies only if $offset > 0.
     */
    public function offset(int $offset) : self
    {
        if ($offset <= 0) {
            return $this;
        }
        
        return $this->branch(
            fn ($q) => $q->offset($offset)
        );
    }

    /**
     * Wrapper method for the underlying limit().
     * 
     * Is applied only if $limit > 0.
     */
    public function limit(int $limit) : self
    {
        if ($limit <= 0) {
            return $this;
        }

        return $this->branch(
            fn ($q) => $q->limit($limit)
        );
    }

    /**
     * Gets chunk based on offset and limit.
     * 
     * Shortcut for offset() + limit().
     */
    public function slice(int $offset, int $limit) : self
    {
        return $this
            ->offset($offset)
            ->limit($limit);
    }

    /**
     * Clears sorting and creates ASC sort step.
     */
    public function orderByAsc(string $field) : self
    {
        $sortOrder = [
            SortStep::asc($field)
        ];

        return $this->withSort($sortOrder);
    }

    /**
     * Clears sorting and creates DESC sort step.
     */
    public function orderByDesc(string $field) : self
    {
        $sortOrder = [
            SortStep::desc($field)
        ];

        return $this->withSort($sortOrder);
    }

    /**
     * Adds ASC sort step.
     */
    public function thenByAsc(string $field) : self
    {
        $sortOrder = $this->sortOrder;
        $sortOrder[] = SortStep::asc($field);

        return $this->withSort($sortOrder);
    }

    /**
     * Adds DESC sort step.
     */
    public function thenByDesc(string $field) : self
    {
        $sortOrder = $this->sortOrder;
        $sortOrder[] = SortStep::desc($field);

        return $this->withSort($sortOrder);
    }

    /**
     * Applies sort order as array.
     *
     * @param SortStep[] $sortOrder
     */
    public function withSort(array $sortOrder) : self
    {
        return $this->branch(null, $sortOrder);
    }

    /**
     * Breaks the search string into words
     * and applies where() with each of them
     * using AND.
     *
     * @param string $searchStr One or several words
     * @param integer $paramCount How many times every word must be passed to where()
     */
    public function search(
        string $searchStr,
        string $where,
        int $paramCount = 1
    ) : self
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

    /**
     * Applies query filters if the condition is true.
     */
    public function applyIf(bool $condition, callable ...$filters) : self
    {
        return $condition
            ? $this->apply(...$filters)
            : $this;
    }

    /**
     * Applies first query filter if the condition is true
     * and second query filter if it's false.
     */
    public function applyIfElse(
        bool $condition,
        callable $ifTrue,
        callable $ifFalse
    ) : self
    {
        return $condition
            ? $this->apply($ifTrue)
            : $this->apply($ifFalse);
    }

    /**
     * Applies query filters.
     * 
     * Filter must accept Query and return Query.
     */
    public function apply(callable ...$filters) : self
    {
        $q = $this;

        foreach ($filters as $filter) {
            $q = ($filter)($q);
        }

        return $q;
    }

    /**
     * Returns page by number and size.
     */
    public function page(int $page, int $pageSize) : self
    {
        if ($page <= 0) {
            $page = 1;
        }

        $limit = ($pageSize > 0) ? $pageSize : 0;
        $offset = ($page - 1) * $limit;

        return $this->slice($offset, $limit);
    }

    // IteratorAggregate

    public function getIterator() : DbModelCollection
    {
        return $this->all();
    }

    // ArrayableInterface

    public function toArray() : array
    {
        return $this->all()->toArray();
    }

    // __toString

    public function toString() : string
    {
        if ($this->isEmpty()) {
            return null;
        }

        return self::queryToString($this->getSortedQuery());
    }

    public function __toString()
    {
        return $this->toString();
    }
}
