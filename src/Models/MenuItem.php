<?php

namespace Plasticode\Models;

use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Interfaces\UpdatedAtInterface;
use Plasticode\Models\Traits\CreatedAt;
use Plasticode\Models\Traits\UpdatedAt;

/**
 * @property integer $id
 * @property integer $menuId
 * @property integer $position
 * @property string $text
 * @method string url()
 * @method static withUrl(string|callable $url)
 */
class MenuItem extends DbModel implements UpdatedAtInterface
{
    use CreatedAt;
    use UpdatedAt;

    protected function requiredWiths(): array
    {
        return ['url'];
    }
}
