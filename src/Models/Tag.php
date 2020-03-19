<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\LinkableInterface;
use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\Util\Strings;

/**
 * @property integer $id
 * @property string $tag
 * @property integer $entityId
 * @property string $entityType
 */
class Tag extends DbModel implements LinkableInterface, SerializableInterface
{
    private ?string $url = null;

    public function withUrl(string $url) : self
    {
        $this->url = $url;
        return $this;
    }

    public function url() : ?string
    {
        return $this->url;
    }
    
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
