<?php

namespace Plasticode\ViewModels;

use Plasticode\Parsing\ContentsItem;

class ContentsViewModel extends ViewModel
{
    /** @var ContentsItem[] */
    private $items;

    /**
     * @param ContentsItem[] $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * Contents items.
     *
     * @return ContentsItem[]
     */
    public function items() : array
    {
        return $this->items;
    }
}
