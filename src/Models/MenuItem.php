<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Util\SortStep;

class MenuItem extends DbModel implements LinkableInterface
{
    public const ParentIdField = 'menu_id';

    /** @var string|null */
    private $url;

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

    public function withUrl(string $url) : self
    {
        $this->url = $url;
        return $this;
    }

    public function url() : ?string
    {
        return $this->url;
    }
}
