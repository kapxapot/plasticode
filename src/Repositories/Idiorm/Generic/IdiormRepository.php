<?php

namespace Plasticode\Repositories\Idiorm\Generic;

use ORM;
use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Data\DbMetadata;
use Plasticode\Data\Rights;
use Plasticode\Data\Query;
use Plasticode\Exceptions\InvalidResultException;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Interfaces\EntityRelatedInterface;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Repositories\Idiorm\Core\RepositoryContext;
use Plasticode\Repositories\Interfaces\Generic\RepositoryInterface;
use Plasticode\Traits\EntityRelated;
use Plasticode\Util\SortStep;

abstract class IdiormRepository implements EntityRelatedInterface, RepositoryInterface
{
    use EntityRelated;

    /**
     * Overriden table name.
     */
    protected string $table = '';

    /**
     * Default sort field name.
     */
    protected string $sortField = '';

    /**
     * Default sort direction.
     */
    protected bool $sortReverse = false;

    private Access $access;
    private AuthInterface $auth;
    private CacheInterface $cache;
    private DbMetadata $dbMetadata;

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
        $this->dbMetadata = $context->dbMetadata();

        $this->hydrator = $hydrator;
    }

    protected function auth(): AuthInterface
    {
        return $this->auth;
    }

    /**
     * Query with default sort.
     */
    protected function query(): Query
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
    private function baseQuery(): Query
    {
        return new Query(
            $this->getDbQuery(),
            fn (ORM $obj) => $this->ormObjToEntity($obj)
        );
    }

    private function getDbQuery(): ORM
    {
        $tableName = $this->dbMetadata->tableName(
            $this->getTable()
        );

        return ORM::forTable($tableName);
    }

    /**
     * Returns sort order.
     *
     * @return SortStep[]
     */
    protected function getSortOrder(): array
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

    public function getCount(): int
    {
        return $this->baseQuery()->count();
    }
    
    /**
     * Returns entity by id.
     * 
     * If the entity is cached, returns it from cache by default.
     */
    protected function getEntity(?int $id, bool $ignoreCache = false): ?DbModel
    {
        if (is_null($id)) {
            return null;
        }

        /** @var DbModel */
        $entity = null;

        if (!$ignoreCache) {
            $entity = $this->getCachedEntity($id);
        }

        $entity ??= $this
            ->baseQuery()
            ->apply(
                fn (Query $q) => $this->filterById($q, $id)
            )
            ->one();

        if (is_null($entity)) {
            $this->deleteCachedEntity($id);
        }

        return $entity;
    }

    /**
     * Returns entity by id ignoring cache.
     */
    protected function reloadEntity(?int $id): ?DbModel
    {
        return $this->getEntity($id, true);
    }

    private function getCachedEntity(int $id): ?DbModel
    {
        $key = $this->entityCacheKey($id);

        return $this->cache->get($key);
    }

    private function cacheEntity(DbModel $entity): void
    {
        $key = $this->entityCacheKey($entity->getId());

        $this->cache->set($key, $entity);
    }

    private function deleteCachedEntity(int $id): void
    {
        $key = $this->entityCacheKey($id);

        $this->cache->delete($key);
    }

    private function entityCacheKey(int $id): string
    {
        return $this->getTable() . '_' . $id;
    }

    /**
     * Creates a bare entity and saves it (hydrating afterwards).
     */
    protected function storeEntity(array $data): DbModel
    {
        $entity = $this->createBareEntity($data);

        return $this->saveEntity($entity);
    }

    /**
     * Saves entity, adds it to the cache and hydrates it.
     */
    protected function saveEntity(DbModel $entity): DbModel
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

    protected function entityToOrmObj(DbModel $entity): ORM
    {
        /** @var ORM|null */
        $ormObj = null;
        
        if ($entity->isPersisted()) {
            $ormObj = $this->findRecord($entity->getId());
        }

        $ormObj ??= $this->createRecord();

        $ormObj->set(
            $entity->toArray()
        );

        return $ormObj;
    }

    private function findRecord(int $id): ?ORM
    {
        return $this
            ->getDbQuery()
            ->where($this->idField(), $id)
            ->findOne();
    }

    private function createRecord(): ORM
    {
        return $this
            ->getDbQuery()
            ->create();
    }

    /**
     * Creates a bare entity and hydrates it.
     */
    protected function createEntity(array $data): DbModel
    {
        $entity = $this->createBareEntity($data);

        return $this->hydrateEntity($entity);
    }

    /**
     * Converts {@see ORM} object to entity.
     * 
     * If the object is present in cache and
     * $updateCache !== true, it is loaded from cache.
     * 
     * Otherwise, the new entity is created, cached and hydrated.
     */
    private function ormObjToEntity(ORM $ormObj, bool $updateCache = false): DbModel
    {
        $id = $ormObj[$this->idField()];
        $entity = $this->getCachedEntity($id);

        if ($entity) {
            // if the entity is cached and doesn't need to be updated,
            // just return it
            if (!$updateCache) {
                return $entity;
            }

            // otherwise the entity must be updated
            $entity = $entity->update(
                $ormObj->asArray()
            );

            return $this->rehydrateEntity($entity);
        }

        // create new entity and cache it
        $entity = $this->createBareEntity(
            $ormObj->asArray()
        );

        $this->cacheEntity($entity);

        return $this->hydrateEntity($entity);
    }

    /**
     * Just creates an entity, no hydration.
     */
    private function createBareEntity(array $data): DbModel
    {
        $entityClass = $this->getEntityClass();
        return $entityClass::create($data);
    }

    protected function hydrateEntity(DbModel $entity): DbModel
    {
        return $entity->hydrate($this->hydrator);
    }

    protected function rehydrateEntity(DbModel $entity): DbModel
    {
        return $entity->hydrate($this->hydrator, true);
    }

    public function tableAccess(): array
    {
        return $this
            ->tableRights()
            ->forTable();
    }

    public function entityAccess(ArrayableInterface $entity): array
    {
        return $this
            ->tableRights()
            ->forEntity($entity->toArray());
    }

    public function can(string $rights): bool
    {
        return $this
            ->tableRights()
            ->can($rights);
    }

    private function tableRights(): Rights
    {
        return $this->access->getTableRights(
            $this->getTable(),
            $this->auth->getUser()
        );
    }

    /**
     * The table name is generated as a plural form of $entityClass var.
     * 
     * Alternatively, the table name can be specified explicitly in $table var.
     */
    public function getTable(): string
    {
        return (strlen($this->table) > 0)
            ? $this->table
            : $this->pluralAlias();
    }

    // filters

    protected function filterById(Query $query, ?int $id): Query
    {
        return $query->where($this->idField(), $id);
    }
}
