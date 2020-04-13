<?php

namespace Plasticode\Models;

use Plasticode\Collection;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property string $link
 * @property string $text
 * @property integer $position
 * @method Collection items()
 * @method string|null url()
 * @method self withItems(Collection|callable $items)
 * @method self withUrl(string|callable|null $url)
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
