<?php

namespace Plasticode\Collections;

use Plasticode\Collections\Basic\ScalarCollection;
use Plasticode\Collections\Basic\TaggedCollection;
use Plasticode\Models\Interfaces\NewsSourceInterface;
use Plasticode\Util\Date;
use Plasticode\Util\Sort;

class NewsSourceCollection extends TaggedCollection
{
    protected string $class = NewsSourceInterface::class;

    /**
     * Sorts news in descending order by publish date.
     * Reverse sort = ascending order.
     * 
     * @return static
     */
    public function sort(bool $reverse = false) : self
    {
        return $this->orderBy(
            fn (NewsSourceInterface $n) => $n->publishedAtIso(),
            $reverse ? Sort::DESC : Sort::ASC,
            Sort::DATE
        );
    }

    /**
     * Sorts news in ascending order by publish date.
     *
     * @return static
     */
    public function sortReverse() : self
    {
        return $this->sort(true);
    }

    public function years() : ScalarCollection
    {
        return $this
            ->scalarize(
                fn (NewsSourceInterface $n) =>
                Date::year(
                    $n->publishedAtIso()
                )
            )
            ->distinct();
    }
}
