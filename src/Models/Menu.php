<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Models\Interfaces\LinkableInterface;

/**
 * @property int $id
 * @property string $link
 * @property string $text
 * @property integer $position
 * @property string $createdAt
 * @property string $updatedAt
 */
class Menu extends DbModel implements LinkableInterface
{
    /** @var Collection */
    private $items;

    /** @var string|null */
    private $url;

    public function withItems(Collection $items) : self
    {
        $this->items = $items;
        return $this;
    }

    public function items() : Collection
    {
        return $this->items;
    }

    public function withUrl(?string $url) : self
    {
        $this->url = $url;
        return $this;
    }

    public function url() : ?string
    {
        return $this->url;
    }
}
