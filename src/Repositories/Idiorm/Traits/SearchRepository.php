<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Search\SearchParams;
use Plasticode\Data\Query;
use Plasticode\Search\SearchResult;

trait SearchRepository
{
    public function applySearchParams(Query $query, SearchParams $searchParams): Query
    {
        return $query
            ->applyIf(
                $searchParams->hasFilter(),
                fn (Query $q) => $this->applyFilter($q, $searchParams->filter())
            )
            ->applyIf(
                $searchParams->hasSort(),
                fn (Query $q) => $q->withSort($searchParams->sort())
            )
            ->applyIf(
                $searchParams->hasOffset(),
                fn (Query $q) => $q->offset($searchParams->offset())
            )
            ->applyIf(
                $searchParams->hasLimit(),
                fn (Query $q) => $q->limit($searchParams->limit())
            );
    }

    abstract protected function applyFilter(Query $query, string $filter): Query;

    // FilteringRepositoryInterface

    public function getSearchResult(SearchParams $searchParams): SearchResult
    {
        return new SearchResult(
            $this->getAllFiltered($searchParams),
            $this->getCount(),
            $this->getFilteredCount($searchParams)
        );
    }

    protected function getAllFiltered(SearchParams $searchParams): DbModelCollection
    {
        $searchQuery = $this->applySearchParams(
            $this->baseQuery(),
            $searchParams
        );

        return DbModelCollection::from($searchQuery);
    }

    protected function getFilteredCount(SearchParams $searchParams): int
    {
        return $this
            ->baseQuery()
            ->applyIf(
                $searchParams->hasFilter(),
                fn (Query $q) => $this->applyFilter($q, $searchParams->filter())
            )
            ->count();
    }
}
