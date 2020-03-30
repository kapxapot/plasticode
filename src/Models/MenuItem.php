<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Traits\WithUrl;
use Plasticode\Util\SortStep;

/**
 * @property integer $position
 * @property string $text
 */
class MenuItem extends DbModel implements LinkableInterface
{
    use WithUrl;

    protected string $parentIdField = 'menu_id';

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
