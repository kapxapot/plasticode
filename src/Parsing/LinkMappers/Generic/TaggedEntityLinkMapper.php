<?php

namespace Plasticode\Parsing\LinkMappers\Generic;

use Plasticode\Parsing\Interfaces\TaggedLinkMapperInterface;
use Plasticode\Parsing\LinkMappers\Traits\SimpleMapSlug;
use Plasticode\Parsing\LinkMappers\Traits\Tagged;

abstract class TaggedEntityLinkMapper extends EntityLinkMapper implements TaggedLinkMapperInterface
{
    use SimpleMapSlug, Tagged;

    public function tag(): string
    {
        return $this->entity();
    }

    protected function renderSlug(string $slug, string $text): ?string
    {
        return $this->renderPlaceholder($slug, $text);
    }
}
