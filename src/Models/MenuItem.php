<?php

namespace Plasticode\Models;

use Plasticode\Util\SortStep;

/**
 * @property integer $position
 * @property string $text
 * @method string url()
 * @method self withUrl(string|callable $url)
 */
class MenuItem extends DbModel
{
    protected string $parentIdField = 'menu_id';

    protected function requiredWiths(): array
    {
        return ['url'];
    }

    /**
     * @return SortStep[]
     */
    protected function getSortOrder() : array
    {
        return [
            SortStep::create('position'),
            SortStep::create('text')
        ];
    }
}
