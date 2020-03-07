<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Data\Rights;
use Plasticode\Models\User;
use Plasticode\Query;

/**
 * Full publish support: published + published_at.
 */
trait FullPublish
{
    use Created;

    use Publish
    {
        Publish::wherePublishedQuery as protected parentWherePublishedQuery;
    }

    protected static $publishedAtField = 'published_at';

    /**
     * Modifies the query to protect access rights if needed.
     * 
     * Entity is filtered out if it isn't published and
     * the user isn't its creator.
     *
     * @param Query $query
     * @param User|null $user
     * @return Query
     */
    public function protectQuery(Query $query, ?User $user) : Query
    {
        $editor = $this->can(Rights::EDIT);
        
        if ($editor) {
            return $query;
        }

        $publishedCondition =
            '(' . static::$publishedField . ' = 1 and ' .
            static::$publishedAtField . ' < now())';

        if ($user) {
            return $query->whereRaw(
                '(' . $publishedCondition . ' or ' .
                static::$createdByField . ' = ?)',
                [$user->getId()]
            );
        }
        
        return $query->whereRaw($publishedCondition);
    }

    public abstract function can(string $rights) : bool;

    protected function wherePublishedQuery(Query $query) : Query
    {
        return $this
            ->parentWherePublishedQuery($query)
            ->whereRaw('(' . static::$publishedAtField . ' < now())');
    }
}
