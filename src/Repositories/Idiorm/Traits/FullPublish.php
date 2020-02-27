<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Data\Rights;
use Plasticode\Models\DbModel;
use Plasticode\Models\User;
use Plasticode\Query;

/**
 * Full publish support: published + published_at.
 * 
 * @method boolean can(string $rights)
 */
trait FullPublish
{
    use Publish
    {
        Publish::wherePublished as protected parentWherePublished;
    }

    protected static $publishedAtField = 'published_at';
    protected static $createdByField = 'created_by';

    /**
     * Looks for a protected record (checks access rights) by id.
     *
     * @param Query $query
     * @param integer|string|null $id
     * @param User|null user
     * @return DbModel|null
     */
    public function findProtected(Query $query, $id, ?User $user) : ?DbModel
    {
        return $this
            ->protect($query, $user)
            ->find($id);
    }

    /**
     * Modifies the query to protect access rights if needed.
     *
     * @param Query $query
     * @param User|null $user
     * @return Query
     */
    public function protect(Query $query, ?User $user) : Query
    {
        $editor = $this->can(Rights::EDIT);
        
        if ($editor) {
            return $query;
        }

        $publishedCondition = '(' . static::$publishedField . ' = 1 and ' . static::$publishedAtField . ' < now())';

        if ($user) {
            return $query->whereRaw(
                '(' . $publishedCondition . ' or ' . static::$createdByField . ' = ?)',
                [$user->getId()]
            );
        }
        
        return $query->whereRaw($publishedCondition);
    }

    protected function wherePublished(Query $query) : Query
    {
        return $this
            ->parentWherePublished($query)
            ->whereRaw('(' . static::$publishedAtField . ' < now())');
    }
}
