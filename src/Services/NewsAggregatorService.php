<?php

namespace Plasticode\Services;

use Plasticode\Collections\NewsSourceCollection;
use Plasticode\Collections\NewsYearCollection;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Models\Interfaces\NewsSourceInterface;
use Plasticode\Models\NewsYear;
use Plasticode\Repositories\Interfaces\Basic\NewsSourceRepositoryInterface as SrcRepoInterface;
use Plasticode\Util\Date;
use Webmozart\Assert\Assert;

class NewsAggregatorService
{
    /** @var SrcRepoInterface[] */
    private array $sources = [];

    /** @var SrcRepoInterface[] */
    private array $strictSources = [];

    private LinkerInterface $linker;

    public function __construct(
        LinkerInterface $linker
    )
    {
        $this->linker = $linker;
    }

    public function registerStrictSource(
        SrcRepoInterface $source
    ) : void
    {
        $this->registerSource($source, true);
    }

    public function registerSource(
        SrcRepoInterface $source,
        bool $strict = false
    ) : void
    {
        $this->sources[] = $source;

        if ($strict) {
            $this->strictSources[] = $source;
        }
    }

    /**
     * Get sources list based on the strictness.
     *
     * @return SrcRepoInterface[]
     */
    private function getSources(bool $strict = false) : array
    {
        return $strict
            ? $this->strictSources
            : $this->sources;
    }

    /**
     * Apply action to sources.
     */
    private function withSources(bool $strict, \Closure $action) : array
    {
        return array_map($action, $this->getSources($strict));
    }

    /**
     * Returns action results as one collection.
     * Action must return a NewsSourceCollection.
     */
    private function collect(bool $strict, \Closure $action) : NewsSourceCollection
    {
        return NewsSourceCollection::merge(
            ...$this->withSources($strict, $action)
        );
    }

    public function getAllByTag(
        string $tag,
        bool $strict = true
    ) : NewsSourceCollection
    {
        return $this
            ->collect(
                $strict,
                fn (SrcRepoInterface $s) => $s->getNewsByTag($tag)
            )
            ->sort();
    }

    public function getCount(bool $strict = false) : int
    {
        $counts = $this->withSources(
            $strict,
            fn (SrcRepoInterface $s) => $s->getNewsCount()
        );

        return array_sum($counts);
    }

    public function getLatest(
        int $limit = 0,
        int $exceptId = 0,
        bool $strict = true
    ) : NewsSourceCollection
    {
        return $this->getPage(1, $limit, $exceptId, $strict);
    }

    public function getPage(
        int $page = 1,
        int $pageSize = 7,
        int $exceptId = 0,
        bool $strict = false
    ) : NewsSourceCollection
    {
        if ($page < 1) {
            $page = 1;
        }

        Assert::greaterThan($pageSize, 0);

        $offset = ($page - 1) * $pageSize;

        // optimization for faster load
        $loadLimit = $offset + $pageSize;

        return $this
            ->collect(
                $strict,
                fn (SrcRepoInterface $s) =>
                $s->getLatestNews($loadLimit, $exceptId)
            )
            ->sortReverse()
            ->slice($offset, $pageSize);
    }

    /**
     * Looks for News or ForumTopic with the provided id.
     */
    public function getNews(?int $newsId) : ?NewsSourceInterface
    {
        foreach ($this->strictSources as $source) {
            $news = $source->getNews($newsId);

            if ($news) {
                return $news;
            }
        }

        return null;
    }

    public function getPrev(
        NewsSourceInterface $news,
        bool $strict = true
    ) : ?NewsSourceInterface
    {
        // todo: this is not a part of interface (!)
        $date = $news->publishedAt;

        if (is_null($date)) {
            return null;
        }

        return $this
            ->collect(
                $strict,
                fn (SrcRepoInterface $s) => $s->getNewsBefore($date, 1)
            )
            ->sort()
            ->first();
    }

    public function getNext(
        NewsSourceInterface $news,
        bool $strict = true
    ) : ?NewsSourceInterface
    {
        // todo: this is not a part of interface (!)
        $date = $news->publishedAt;

        if (is_null($date)) {
            return null;
        }

        return $this
            ->collect(
                $strict,
                fn (SrcRepoInterface $s) => $s->getNewsAfter($date, 1)
            )
            ->sortReverse()
            ->first();
    }

    private function getAllRaw(bool $strict = false) : NewsSourceCollection
    {
        return $this->collect(
            $strict,
            fn (SrcRepoInterface $s) => $s->getLatestNews()
        );
    }

    public function getTop(int $limit, bool $strict = false) : NewsSourceCollection
    {
        return $this->getPage(1, $limit, 0, $strict);
    }

    /**
     * Descending.
     */
    public function getYears(bool $strict = true) : NewsYearCollection
    {
        $years = $this
            ->getAllRaw($strict)
            ->years()
            ->map(
                fn (int $y) =>
                new NewsYear(
                    $y,
                    $this->linker->newsYear($y)
                )
            );

        return NewsYearCollection::from($years)->sort();
    }

    public function getPrevYear(int $year, bool $strict = true) : ?NewsYear
    {
        return $this->getYears($strict)
            ->where(
                fn (NewsYear $y) => $y->year < $year
            )
            ->desc('year')
            ->first();
    }

    public function getNextYear(int $year, bool $strict = true) : ?NewsYear
    {
        return $this->getYears($strict)
            ->where(
                fn (NewsYear $y) => $y->year > $year
            )
            ->asc('year')
            ->first();
    }

    public function getByYear(int $year, bool $strict = true) : array
    {
        $byYear = $this
            ->collect(
                $strict,
                fn (SrcRepoInterface $s) => $s->getNewsByYear($year)
            )
            ->sort();

        $monthly = [];

        /** @var NewsSourceInterface $s */
        foreach ($byYear as $s) {
            $month = Date::month($s->publishedAtIso());

            if (!array_key_exists($month, $monthly)) {
                $monthly[$month] = [
                    'label' => Date::SHORT_MONTHS[$month],
                    'full_label' => Date::MONTHS[$month],
                    'news' => [],
                ];
            }

            $monthly[$month]['news'][] = $s;
        }

        ksort($monthly);

        return $monthly;
    }
}
