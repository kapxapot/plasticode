<?php

namespace Plasticode\Data;

use Countable;
use IteratorAggregate;
use ORM;
use PDOException;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Exceptions\SqlException;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Util\Arrays;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;
use ReflectionMethod;
use Webmozart\Assert\Assert;

/**
 * Idiorm ({@see ORM}) wrapper integrated with {@see DbModel}.
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
class Query implements ArrayableInterface, Countable, IteratorAggregate
{
    /**
     * Empty query.
     */
    private static ?self $empty = null;

    /**
     * ORM query.
     */
    private ?ORM $query = null;

    /**
     * Method for conversion of dbObj to model.
     *
     * @var callable|null
     */
    private $toModel = null;

    /**
     * Array of sort steps.
     *
     * @var SortStep[]
     */
    private array $sortOrder = [];

    /**
     * Query log (if enabled).
     *
     * @var string[]
     */
    private static array $log = [];

    private static bool $logEnabled = false;

    /**
     * @param ORM|null $query The base query. Can be null for an empty query.
     * @param callable|null $toModel Must be provided for non-empty query.
     * @param SortStep[]|null $sortOrder
     */
    public function __construct(
        ?ORM $query = null,
        ?callable $toModel = null,
        ?array $sortOrder = null
    )
    {
        if ($query === null) {
            return;
        }

        $this->query = $query;

        Assert::notNull(
            $toModel,
            'Non-empty query requires toModel() function!'
        );

        $this->toModel = $toModel;
        $this->sortOrder = $sortOrder ?? [];
    }

    public static function enableLog(): void
    {
        self::$logEnabled = true;
    }

    /**
     * Returns query log.
     *
     * @return string[]
     */
    public static function getLog(): array
    {
        return self::$log;
    }

    /**
     * Get underlying {@see ORM} query.
     */
    public function getOrmQuery(): ?ORM
    {
        return $this->query;
    }

    /**
     * Returns "empty" query (without table and filters).
     *
     * @return static
     */
    public static function empty(): self
    {
        self::$empty ??= new static();

        return self::$empty;
    }

    /**
     * Checks if the query is empty (without table and filters).
     */
    public function isEmpty(): bool
    {
        return $this->query === null;
    }

    /**
     * Executes query and returns all entities.
     *
     * "Select all".
     * In case of empty Query returns empty collection.
     */
    public function all(bool $ignoreCache = false): DbModelCollection
    {
        if ($this->isEmpty()) {
            return DbModelCollection::empty();
        }

        $query = $this->getSortedQuery();

        try {
            $objs = $query->findMany();

            self::logQuery($query, 'findMany');

            $all = array_map(
                fn (ORM $obj) => ($this->toModel)($obj, $ignoreCache),
                $objs ?? []
            );

            return DbModelCollection::make($all);
        } catch (PDOException $pdoEx) {
            throw new SqlException(
                'Failed to execute query: ' . self::queryToString($query),
                0,
                $pdoEx
            );
        }
    }

    /**
     * Gets SQL statement for the {@see ORM} query.
     */
    private static function queryToString(ORM $query): string
    {
        $method = new ReflectionMethod(ORM::class, '_build_select');
        $method->setAccessible(true);

        $statement = $method->invoke($query);

        return $statement;
    }

    /**
     * Executes query and returns the first entity if any.
     *
     * "Select one".
     * In case of empty query returns `null`.
     */
    public function one(bool $ignoreCache = false): ?DbModel
    {
        if ($this->isEmpty()) {
            return null;
        }

        $query = $this->getSortedQuery();

        $obj = $query->findOne();

        self::logQuery($query, 'findOne');

        return $obj
            ? ($this->toModel)($obj, $ignoreCache)
            : null;
    }

    /**
     * Returns query with applied sort order.
     */
    private function getSortedQuery(): ORM
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
     * In case of an empty query (or no records) returns `null`.
     *
     * Note:
     *
     * If the query results are changed during the execution
     * of this function, the function tries to get the first record.
     *
     * This can be when:
     *
     * - There were records on the counting step, but they disappered
     * before getting the random record.
     * - The count was 0 but before returning the empty record, some
     * records have appeared.
     */
    public function random(): ?DbModel
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
     * Executes `count()` query and checks if there are any records.
     */
    public function any(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Executes query and returns record count.
     *
     * "Select count(*)".
     * In case of empty query returns 0.
     */
    public function count(): int
    {
        if ($this->isEmpty()) {
            return 0;
        }

        $query = $this->query;
        $count = $query->count();

        self::logQuery($query, 'count');

        return $count;
    }

    /**
     * Deletes records based on the query.
     *
     * "Delete all".
     * Returns the result of the operation. In case of empty query returns `null`.
     */
    public function delete(): ?bool
    {
        if ($this->isEmpty()) {
            return null;
        }

        $query = $this->query;
        $result = $query->deleteMany();

        self::logQuery($query, 'deleteMany');

        return $result;
    }

    private static function logQuery(ORM $query, string $description): void
    {
        if (!self::$logEnabled) {
            return;
        }

        $queryStr = self::queryToString($query);

        self::$log[] = sprintf('%s (%s)', $queryStr, $description);
    }

    /**
     * Delegates method call to the underlying {@see ORM} query.
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        return $this->branch(
            fn (ORM $q) => $q->{$name}(...$args)
        );
    }

    /**
     * Creates new Query based on the current one
     * plus applied modification.
     *
     * @param SortStep[]|null $sortOrder
     * @return mixed
     */
    private function branch(
        ?callable $queryModifier = null,
        ?array $sortOrder = null
    )
    {
        if ($this->isEmpty()) {
            return $this;
        }

        if ($queryModifier !== null) {
            $result = $queryModifier($this->query);

            // if query resulted in any final result (!= query)
            // return it as is
            if (!($result instanceof ORM)) {
                return $result;
            }
        }

        // if query modification resulted in another query
        // wrap it and return as a new Query
        return new Query(
            $result ?? $this->query,
            $this->toModel,
            $sortOrder ?? $this->sortOrder
        );
    }

    /**
     * Wrapper method for the underlying `whereIn()`.
     *
     * Allows passing an array or a {@see ArrayableInterface}.
     * In case of empty array returns empty query!
     *
     * @param array|ArrayableInterface $values
     */
    public function whereIn(string $field, $values): self
    {
        $values = Arrays::adopt($values);

        if (empty($values)) {
            return self::empty();
        }

        return $this->branch(
            fn (ORM $q) => $q->whereIn($field, $values)
        );
    }

    /**
     * Wrapper method for the underlying `whereNotIn()`.
     *
     * Allows passing an array or a {@see ArrayableInterface}.
     *
     * @param array|ArrayableInterface $values
     */
    public function whereNotIn(string $field, $values): self
    {
        $values = Arrays::adopt($values);

        if (empty($values)) {
            return $this;
        }

        return $this->branch(
            fn (ORM $q) => $q->whereNotIn($field, $values)
        );
    }

    /**
     * Gets a chunk based on the offset and the limit.
     *
     * Shortcut for `offset()->limit()`.
     */
    public function slice(int $offset, int $limit): self
    {
        return $this
            ->offset($offset)
            ->limit($limit);
    }

    /**
     * Wrapper method for the underlying `offset()`.
     *
     * Applies only if `$offset > 0`.
     */
    public function offset(int $offset): self
    {
        if ($offset <= 0) {
            return $this;
        }
        
        return $this->branch(
            fn (ORM $q) => $q->offset($offset)
        );
    }

    /**
     * Wrapper method for the underlying `limit()`.
     *
     * Is applied only if `$limit > 0`.
     */
    public function limit(int $limit): self
    {
        if ($limit <= 0) {
            return $this;
        }

        return $this->branch(
            fn (ORM $q) => $q->limit($limit)
        );
    }

    /**
     * Clears sorting and creates an ASC sort step.
     */
    public function orderByAsc(string $field): self
    {
        $sortOrder = [
            SortStep::asc($field)
        ];

        return $this->withSort($sortOrder);
    }

    /**
     * Clears sorting and creates a DESC sort step.
     */
    public function orderByDesc(string $field): self
    {
        $sortOrder = [
            SortStep::desc($field)
        ];

        return $this->withSort($sortOrder);
    }

    /**
     * Adds an ASC sort step.
     */
    public function thenByAsc(string $field): self
    {
        $sortOrder = $this->sortOrder;
        $sortOrder[] = SortStep::asc($field);

        return $this->withSort($sortOrder);
    }

    /**
     * Adds a DESC sort step.
     */
    public function thenByDesc(string $field): self
    {
        $sortOrder = $this->sortOrder;
        $sortOrder[] = SortStep::desc($field);

        return $this->withSort($sortOrder);
    }

    /**
     * Applies the sort order as an array.
     *
     * @param SortStep[] $sortOrder
     */
    public function withSort(array $sortOrder): self
    {
        return $this->branch(null, $sortOrder);
    }

    /**
     * Breaks the search string into words and applies `where()` with each of them
     * using AND.
     *
     * @param string $searchStr One or several words.
     * @param integer $paramCount How many times every word must be passed to `where()`.
     */
    public function search(
        string $searchStr,
        string $where,
        int $paramCount = 1
    ): self
    {
        return $this->branch(
            function (ORM $q) use ($searchStr, $where, $paramCount) {
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
     * Applies query filters if the condition is `true`.
     */
    public function applyIf(bool $condition, callable ...$filters): self
    {
        return $condition
            ? $this->apply(...$filters)
            : $this;
    }

    /**
     * Applies first query filter if the condition is `true`
     * and second query filter if it's `false`.
     */
    public function applyIfElse(
        bool $condition,
        callable $ifTrue,
        callable $ifFalse
    ): self
    {
        return $condition
            ? $this->apply($ifTrue)
            : $this->apply($ifFalse);
    }

    /**
     * Applies query filters.
     *
     * Filter must accept {@see Query} and return {@see Query}.
     */
    public function apply(callable ...$filters): self
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
    public function page(int $page, int $pageSize): self
    {
        if ($page <= 0) {
            $page = 1;
        }

        $limit = ($pageSize > 0) ? $pageSize : 0;
        $offset = ($page - 1) * $limit;

        return $this->slice($offset, $limit);
    }

    /**
     * Returns query count.
     */
    public static function getQueryCount(): int
    {
        return ORM::forTable(null)
            ->rawQuery('SHOW STATUS LIKE ?', ['Questions'])
            ->findOne()['Value'];
    }

    // IteratorAggregate

    public function getIterator(): DbModelCollection
    {
        return $this->all();
    }

    // ArrayableInterface

    public function toArray(): array
    {
        return $this->all()->toArray();
    }

    // __toString

    public function __toString()
    {
        return $this->toString();
    }

    public function toString(): string
    {
        if ($this->isEmpty()) {
            return null;
        }

        return self::queryToString($this->getSortedQuery());
    }
}
