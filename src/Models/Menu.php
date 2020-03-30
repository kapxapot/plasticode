<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;
use Plasticode\Models\Traits\WithUrl;
use Webmozart\Assert\Assert;

/**
 * @property string $link
 * @property string $text
 * @property integer $position
 */
class Menu extends DbModel implements LinkableInterface
{
    use CreatedAt, UpdatedAt, WithUrl;

    protected ?Collection $items = null;

    private bool $itemsInitialized = false;

    public function withItems(Collection $items) : self
    {
        $this->items = $items;
        $this->itemsInitialized = true;

        return $this;
    }

    public function items() : Collection
    {
        Assert::true($this->itemsInitialized);

        return $this->items;
    }
}
