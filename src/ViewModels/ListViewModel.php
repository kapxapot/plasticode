<?php

namespace Plasticode\ViewModels;

/**
 * BB list view model.
 */
class ListViewModel extends ViewModel
{
    private $items;
    private $ordered;

    /**
     * @param string[] $items
     * @param boolean $ordered
     */
    public function __construct(array $items, bool $ordered = null)
    {
        parent::__construct();

        $this->items = $items ?? [];
        $this->ordered = $ordered ?? false;
    }

    /**
     * List items.
     *
     * @return string[]
     */
    public function items() : array
    {
        return $this->items;
    }

    /**
     * Is the list ordered?
     *
     * @return boolean
     */
    public function ordered() : bool
    {
        return $this->ordered;
    }
}
