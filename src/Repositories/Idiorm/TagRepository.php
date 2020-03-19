<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Collection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Data\Db;
use Plasticode\Models\Tag;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class TagRepository extends IdiormRepository implements TagRepositoryInterface
{
    protected $entityClass = Tag::class;

    /** @var LinkerInterface */
    private $linker;

    public function __construct(
        Db $db,
        LinkerInterface $linker
    )
    {
        parent::__construct($db);

        $this->linker = $linker;
    }

    protected function ormObjToEntity(\ORM $ormObj) : Tag
    {
        /** @var Tag */
        $tag = parent::ormObjToEntity($ormObj);

        return $tag
            ->withUrl(
                $this->linker->tag($tag->tag)
            );
    }

    protected function getByEntityQuery(string $entityType, int $entityId) : Query
    {
        return $this
            ->entityQuery($entityType)
            ->where('entity_id', $entityId);
    }

    public function getIdsByTag(string $entityType, string $tag) : Collection
    {
        return $this
            ->entityQuery($entityType)
            ->where('tag', $tag)
            ->all()
            ->extract('entity_id');
    }

    public function deleteByEntity(string $entityType, int $entityId) : bool
    {
        return $this
            ->entityQuery($entityType)
            ->where('entity_id', $entityId)
            ->delete();
    }
    
    public function getByTag(string $tag) : Collection
    {
        return $this->byTagQuery($tag)->all();
    }

    public function exists(string $tag) : bool
    {
        return $this->byTagQuery($tag)->any();
    }

    private function entityQuery(string $entityType) : Query
    {
        return $this
            ->query()
            ->where('entity_type', $entityType);
    }

    private function byTagQuery(string $tag) : Query
    {
        return $this
            ->query()
            ->where('tag', $tag);
    }

    public function search(string $searchQuery) : Collection
    {
        return $this
            ->query()
            ->search($searchQuery, '(tag like ?)')
            ->orderByAsc('tag')
            ->all();
    }

    public function store(array $data) : Tag
    {
        return $this->storeEntity($data);
    }
}
