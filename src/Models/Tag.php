<?php

namespace Plasticode\Models;

use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\Util\Strings;

/**
 * @property string $tag
 * @property integer $entityId
 * @property string $entityType
 * @method string|null url()
 * @method self withUrl(string|callable|null $url)
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
