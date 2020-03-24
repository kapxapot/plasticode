<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Data\Rights;
use Plasticode\Models\User;
use Plasticode\Query;

/**
 * Full publish support: published + published_at.
 */
trait FullPublishedRepository
{
    use CreatedRepository;

    use PublishedRepository
    {
        PublishedRepository::wherePublishedQuery as protected parentWherePublishedQuery;
    }

    protected string $publishedAtField = 'published_at';

    /**
     * Modifies the query to protect access rights if needed.
     * 
     * Entity is filtered out if it isn't published and
     * the user isn't its creator.
     */
    public function protectQuery(Query $query, ?User $user) : Query
    {
        $editor = $this->can(Rights::EDIT);
        
        if ($editor) {
            return $query;
        }

        $publishedCondition =
            '(' . $this->publishedField . ' = 1 and ' .
            $this->publishedAtField . ' < now())';

        if ($user) {
            return $query->whereRaw(
                '(' . $publishedCondition . ' or ' .
                $this->createdByField . ' = ?)',
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
            ->whereRaw('(' . $this->publishedAtField . ' < now())');
    }
}
