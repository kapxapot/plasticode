<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\Util\Strings;

/**
 * @property string $tag
 * @property integer $entityId
 * @property string $entityType
 * @method string url()
 * @method self withUrl(string|callable $url)
 */
class Tag extends DbModel implements SerializableInterface
{
    protected function requiredWiths(): array
    {
        return ['url'];
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
