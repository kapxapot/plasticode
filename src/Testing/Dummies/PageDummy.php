<?php

namespace Plasticode\Testing\Dummies;

use Plasticode\Models\Interfaces\PageInterface;
use Plasticode\Models\NewsSource;
use Plasticode\Util\Strings;

/**
 * @property integer $id
 * @property string $slug
 * @property string $title
 * @property string|null $text
 */
class PageDummy extends NewsSource implements PageInterface
{
    public function getSlug() : string
    {
        return $this->slug;
    }

    public function displayTitle() : string
    {
        return $this->title;
    }

    public function rawText() : ?string
    {
        return $this->text;
    }

    public function code() : string
    {
        $parts[] = $this->slug;

        if ($this->title !== $this->slug) {
            $parts[] = $this->title;
        }

        return Strings::doubleBracketsTag(null, ...$parts);
    }
}
