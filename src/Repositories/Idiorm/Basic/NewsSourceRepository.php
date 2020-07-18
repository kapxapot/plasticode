<?php

namespace Plasticode\Repositories\Idiorm\Basic;

use Plasticode\Collections\NewsSourceCollection;
use Plasticode\Models\Interfaces\NewsSourceInterface;
use Plasticode\Query;
use Plasticode\Repositories\Idiorm\Traits\ProtectedRepository;
use Plasticode\Repositories\Interfaces\Basic\NewsSourceRepositoryInterface;

abstract class NewsSourceRepository extends TaggedRepository implements NewsSourceRepositoryInterface
{
    use ProtectedRepository;

    protected string $sortField = 'published_at';
    protected bool $sortReverse = true;

    // TaggedRepositoryInterface

    public function getAllByTag(string $tag, int $limit = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this->filterByTag(
                $this->publishedQuery(),
                $tag,
                $limit
            )
        );
    }

    // NewsSourceRepositoryInterface

    public function getNewsByTag(string $tag, int $limit = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this->filterByTag(
                $this->newsSourceQuery(),
                $tag,
                $limit
            )
        );
    }

    public function getLatestNews(int $limit = 0, int $exceptId = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this->latestQuery($limit, $exceptId)
        );
    }

    public function getNewsCount() : int
    {
        return $this
            ->latestQuery()
            ->count();
    }

    public function getNewsBefore(string $date, int $limit = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->latestQuery($limit)
                ->whereLt($this->publishedAtField, $date)
                ->orderByDesc($this->publishedAtField)
        );
    }

    public function getNewsAfter(string $date, int $limit = 0) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->latestQuery($limit)
                ->whereGt($this->publishedAtField, $date)
                ->orderByAsc($this->publishedAtField)
        );
    }

    public function getNewsByYear(int $year) : NewsSourceCollection
    {
        return NewsSourceCollection::from(
            $this
                ->newsSourceQuery()
                ->whereRaw(
                    '(year(' . $this->publishedAtField . ') = ?)',
                    [$year]
                )
        );
    }

    public function getNews(?int $id) : ?NewsSourceInterface
    {
        return $this->getProtected($id);
    }

    abstract function getProtected(?int $id) : ?NewsSourceInterface;

    // queries

    protected function latestQuery(int $limit = 0, int $exceptId = 0) : Query
    {
        return $this
            ->newsSourceQuery()
            ->applyIf(
                $exceptId > 0,
                fn (Query $q) => $q->whereNotEqual($this->idField(), $exceptId)
            )
            ->limit($limit);
    }

    /**
     * Override this if needed.
     */
    protected function newsSourceQuery() : Query
    {
        return $this->publishedQuery();
    }
}
