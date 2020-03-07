<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Collection;
use Plasticode\Data\Db;
use Plasticode\Data\Rights;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Repositories\Interfaces\RepositoryInterface;
use Plasticode\Traits\LazyCache;
use Plasticode\Util\Classes;
use Plasticode\Util\Pluralizer;
use Plasticode\Util\SortStep;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

abstract class IdiormRepository implements RepositoryInterface
{
    use LazyCache;

    /**
     * Table name
     *
     * @var string|null
     */
    protected $table;

    /**
     * Entity class name
     *
     * @var string|null
     */
    protected $entityClass;
    
    /**
     * Id field name
     *
     * @var string
     */
    protected $idField = 'id';

    /**
     * Tags field name
     *
     * @var string
     */
    protected $tagsField = 'tags';

    /**
     * Default sort field name
     *
     * @var string|null
     */
    protected $sortField = null;

    /**
     * Default sort direction
     *
     * @var boolean
     */
    protected $sortReverse = false;

    /** @var Db */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
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
    protected function baseQuery() : Query
    {
        $dbQuery = $this->db->forTable(
            $this->getTable()
        );
        
        $toModel = function (\ORM $obj) {
            return $this->ormObjToEntity($obj);
        };
        
        return new Query($dbQuery, $this->idField, $toModel);
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
            new SortStep($this->sortField, $this->sortReverse)
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
     * Shortcut for getting model by id.
     * 
     * @param integer|string|null $id
     * @param boolean $ignoreCache
     * @return DbModel|null
     */
    protected function getEntity($id, bool $ignoreCache = false) : ?DbModel
    {
        if (is_null($id)) {
            return null;
        }

        $name = $this->getTable() . $id;
        
        return self::staticLazy(
            function () use ($id) {
                return $this->baseQuery()->find($id);
            },
            $name,
            $ignoreCache
        );
    }

    protected function saveEntity(DbModel $entity) : DbModel
    {
        $ormObj = $this->entityToOrmObj($entity);

        return $this->saveOrmObj($ormObj);
    }

    private function entityToOrmObj(DbModel $entity) : \ORM
    {
        $obj = $entity->getObj();

        return $obj instanceof \ORM
            ? $obj
            : $this->db->create($this->getTable(), $obj);
    }

    protected function saveOrmObj(\ORM $ormObj) : DbModel
    {
        $ormObj->save();

        return $this->ormObjToEntity($ormObj);
    }

    /**
     * Shortcut for create() + save().
     * 
     * @param array|\ORM $obj
     * @return DbModel
     */
    protected function storeEntity($obj = null) : DbModel
    {
        $entity = $this->createEntity($obj);

        return $this->saveEntity($entity);
    }

    /**
     * @param array|\ORM $obj
     * @return DbModel
     */
    protected function createEntity($obj = null) : DbModel
    {
        $entityClass = $this->getEntityClass();

        return $entityClass::create($obj);
    }

    private function ormObjToEntity(\ORM $ormObj) : DbModel
    {
        $entityClass = $this->getEntityClass();

        return $entityClass::fromDbObj($ormObj);
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
        return $this->db->getTableRights(
            $this->getTable()
        );
    }

    /**
     * Repository MUST be named as '{entity_class}Repository'.
     * The table name is generated as a plural form of 'entity_class'.
     * 
     * Alternatively, the table name can be specified explicitly in static $table var.
     *
     * @return string
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
