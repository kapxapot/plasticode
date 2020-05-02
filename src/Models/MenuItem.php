<?php

namespace Plasticode\Models;

/**
 * @property integer $menuId
 * @property integer $position
 * @property string $text
 * @method string url()
 * @method static withUrl(string|callable $url)
 */
class MenuItem extends DbModel
{
    protected function requiredWiths(): array
    {
        return ['url'];
    }
}
