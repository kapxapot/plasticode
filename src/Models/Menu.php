<?php

namespace Plasticode\Models;

use Plasticode\Collections\MenuItemCollection;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $id
 * @property string $link
 * @property integer $position
 * @property string $text
 * @method MenuItemCollection items()
 * @method string url()
 * @method static withItems(MenuItemCollection|callable $items)
 * @method static withUrl(string|callable $url)
 */
class Menu extends DbModel
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['items', 'url'];
    }
}
