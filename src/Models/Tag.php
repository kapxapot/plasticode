<?php

namespace Plasticode\Models;

use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Interfaces\SerializableInterface;
use Plasticode\Util\Strings;

/**
 * @property integer $entityId
 * @property string $entityType
 * @property integer $id
 * @property string $tag
 * @method string url()
 * @method static withUrl(string|callable $url)
 */
class Tag extends DbModel implements SerializableInterface
{
    protected function requiredWiths(): array
    {
        return ['url'];
    }

    public function serialize(): array
    {
        return [
            'id' => $this->getId(),
            'tag' => $this->tag,
        ];
    }

    public function code(): string
    {
        return Strings::doubleBracketsTag('tag', $this->tag);
    }
}
