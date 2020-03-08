<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Util\SortStep;

class MenuItem extends DbModel implements LinkableInterface
{
    public const ParentIdField = 'menu_id';

    /**
     * @return SortStep[]
     */
    protected static function getSortOrder() : array
    {
        return [
            SortStep::create('position'),
            SortStep::create('text')
        ];
    }

    public function url() : ?string
    {
        return self::$container->linker->rel($this->link);
    }
}
