<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Collection;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Data\Db;
use Plasticode\Data\Rights;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Util\Classes;
use Plasticode\Util\Pluralizer;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

abstract class IdiormRepository
{
    /**
     * Overriden table name
     */
    protected string $table = '';

    /**
     * Full entity class name
     */
    protected string $entityClass = '';

    /**
     * Default sort field name
     */
    protected string $sortField = '';

    /**
     * Default sort direction
     */
    protected bool $sortReverse = false;

    private Access $access;
    private AuthInterface $auth;
    private CacheInterface $cache;
    private Db $db;

    /** @var HydratorInterface|ObjectProxy|null */
    private $hydrator;

    /**
     * @param HydratorInterface|ObjectProxy|null $hydrator
     */
    public function __construct(
        RepositoryContext $context,
        $hydrator = null
    )
    {
        $this->access = $context->access();
        $this->auth = $context->auth();
        $this->cache = $context->cache();
        $this->db = $context->db();

        $this->hydrator = $hydrator;
    }

    protected function idField() : string
    {
        $entityClass = $this->getEntityClass();
        return $entityClass::idField();
    }

    /**
     * Query with default sort.
     */
    protected function query() : Query
    {
        $query = $this->baseQuery();
        $sortOrder = $this->getSortOrder();

        return !empty($sortOrder)
            ? $query->withSort($sortOrder)
            : $query;
    }

    /**
     * Bare query without sort.
     */
    private function baseQuery() : Query
    {
        $dbQuery = $this->db->forTable(
            $this->getTable()
        );

        return new Query(
            $dbQuery,
            $this->idField(),
            fn (\ORM $obj) => $this->ormObjToEntity($obj)
        );
    }

    private function getEntityClass() : ?string
    {
        Assert::subclassOf($this->entityClass, DbModel::class);

        return $this->entityClass;
    }

    /**
     * Returns sort order.
     *
     * @return SortStep[]
     */
    protected function getSortOrder() : array
    {
        if (strlen($this->sortField) == 0) {
            return [];
        }

        return [
            $this->sortReverse
                ? SortStep::desc($this->sortField)
                : SortStep::asc($this->sortField)
        ];
    }

    /**
     * Shortcut for getting all models.
     */
    public function getAll() : Collection
    {
        return $this->query()->all();
    }

    public function getCount() : int
    {
        return $this->baseQuery()->count();
    }
    
    /**
     * Returns entity by id.
     * 
     * If the entity is cached, returns it from cache by default.
     */
    protected function getEntity(?int $id, bool $ignoreCache = false) : ?DbModel
    {
        if (is_null($id)) {
            return null;
        }

        /** @var DbModel */
        $entity = null;

        if (!$ignoreCache) {
            $entity = $this->getCachedEntity($id);
        }

        $entity ??= $this->baseQuery()->find($id);

        if (is_null($entity)) {
            $this->deleteCachedEntity($id);
        }

        return $entity;
    }

    /**
     * Returns entity by id ignoring cache.
     */
    protected function reloadEntity(?int $id) : ?DbModel
    {
        return $this->getEntity($id, true);
    }

    private function entityCacheKey(int $id) : string
    {
        return $this->getTable() . '_' . $id;
    }

    private function getCachedEntity(int $id) : ?DbModel
    {
        $key = $this->entityCacheKey($id);

        return $this->cache->get($key);
    }

    private function cacheEntity(DbModel $entity) : void
    {
        $key = $this->entityCacheKey($entity->getId());

        $this->cache->set($key, $entity);
    }

    private function deleteCachedEntity(int $id) : void
    {
        $key = $this->entityCacheKey($id);

        $this->cache->delete($key);
    }

    /**
     * Saves entity, adds it to cache and hydrates it.
     */
    protected function saveEntity(DbModel $entity) : DbModel
    {
        $ormObj = $this->entityToOrmObj($entity);

        $saveResult = $ormObj->save();

        if (!$saveResult) {
            throw new InvalidResultException(
                'Failed to save the entity.'
            );
        }

        return $this->ormObjToEntity($ormObj, true);
    }

    protected function entityToOrmObj(DbModel $entity) : \ORM
    {
        $obj = $entity->getObj();

        return $obj instanceof \ORM
            ? $obj
            : $this->db->create($this->getTable(), $obj);
    }

    /**
     * Creates a bare entity and saves it (hydrating afterwards).
     * 
     * @param array|\ORM|null $obj
     */
    protected function storeEntity($obj = null) : DbModel
    {
        $entity = $this->createBareEntity($obj);

        return $this->saveEntity($entity);
    }

    /**
     * Just creates an entity, no hydration.
     *
     * @param array|\ORM|null $obj
     */
    private function createBareEntity($obj = null) : DbModel
    {
        $entityClass = $this->getEntityClass();
        return $entityClass::create($obj);
    }

    /**
     * Creates an entity and hydrates it.
     * 
     * @param array|\ORM|null $obj
     */
    protected function createEntity($obj = null) : DbModel
    {
        $entity = $this->createBareEntity($obj);

        return $this->hydrateEntity($entity);
    }

    protected function hydrateEntity(DbModel $entity) : DbModel
    {
        return $entity->hydrate($this->hydrator);
    }

    protected function rehydrateEntity(DbModel $entity) : DbModel
    {
        return $entity->hydrate($this->hydrator, true);
    }

    /**
     * Converts ORM object to entity.
     * 
     * If the object is present in cache and
     * $ignoreCache != true, it is loaded from cache.
     * 
     * Otherwise, the new entity is created, cached and hydrated.
     */
    private function ormObjToEntity(
        \ORM $ormObj,
        bool $ignoreCache = false
    ) : DbModel
    {
        /** @var DbModel */
        $entity = null;

        if (!$ignoreCache) {
            $id = $ormObj[$this->idField()];
            $entity = $this->getCachedEntity($id);
        }

        if ($entity) {
            return $entity;
        }

        $entity = $this->createBareEntity($ormObj);

        $this->cacheEntity($entity);

        return $this->hydrateEntity($entity);
    }

    public function tableAccess() : array
    {
        return $this
            ->tableRights()
            ->forTable();
    }

    public function entityAccess(ArrayableInterface $entity) : array
    {
        return $this
            ->tableRights()
            ->forEntity($entity->toArray());
    }

    public function can(string $rights) : bool
    {
        return $this
            ->tableRights()
            ->can($rights);
    }

    private function tableRights() : Rights
    {
        return $this->access->getEntityRights(
            $this->getTable(),
            $this->auth->getUser()
        );
    }

    /**
     * Repository MUST be named as '{entity_class}Repository'.
     * The table name is generated as a plural form of 'entity_class'.
     * 
     * Alternatively, the table name can be specified explicitly in static $table var.
     */
    public function getTable() : string
    {
        if (strlen($this->table) > 0) {
            return $this->table;
        }

        // \Plasticode\..\ArticleCategoryRepository
        // -> ArticleCategoryRepository
        $class = Classes::shortName(static::class);

        $suffix = 'Repository';

        Assert::true(Strings::endsWith($class, $suffix));

        // ArticleCategoryRepository -> ArticleCategory
        $entityClass = Strings::trimEnd($class, $suffix);

        // ArticleCategory -> ArticleCategories
        $entityPlural = Pluralizer::plural($entityClass);

        // ArticleCategories -> article_categories
        $table = Strings::toSnakeCase($entityPlural);

        return $table;
    }
}
