<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\Models\Traits\WithUrl;
use Plasticode\Util\Strings;

/**
 * @property string $tag
 * @property integer $entityId
 * @property string $entityType
 */
class Tag extends DbModel implements LinkableInterface, SerializableInterface
{
    use WithUrl;

    public function serialize() : array
    {
        return [
            'id' => $this->getId(),
            'tag' => $this->tag,
        ];
    }

    public function code() : string
    {
        return Strings::doubleBracketsTag('tag', $this->tag);
    }
}
