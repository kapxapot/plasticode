<?php

namespace Plasticode\Models;

use Plasticode\Collections\TagLinkCollection;
use Plasticode\Models\Basic\DbModel;
use Plasticode\Models\Interfaces\NewsSourceInterface;
use Plasticode\Models\Interfaces\SearchableInterface;
use Plasticode\Models\Traits\FullPublished;
use Plasticode\Models\Traits\Linkable;
use Plasticode\Models\Traits\Stamps;
use Plasticode\Models\Traits\Tagged;
use Plasticode\Parsing\ParsingContext;

/**
 * @method ParsingContext parsed()
 * @method TagLinkCollection tagLinks()
 * @method static withFullText(string|callable|null $fullText)
 * @method static withParsed(ParsingContext|callable $parsed)
 * @method static withShortText(string|callable|null $shortText)
 * @method static withTagLinks(TagLinkCollection|callable $tagLinks)
 */
abstract class NewsSource extends DbModel implements NewsSourceInterface, SearchableInterface
{
    use FullPublished;
    use Linkable;
    use Stamps;
    use Tagged;

    protected string $fullTextPropertyName = 'fullText';
    protected string $parsedPropertyName = 'parsed';
    protected string $shortTextPropertyName = 'shortText';

    protected function requiredWiths(): array
    {
        return [
            $this->creatorPropertyName,
            $this->fullTextPropertyName,
            $this->parsedPropertyName,
            $this->shortTextPropertyName,
            $this->tagLinksPropertyName,
            $this->updaterPropertyName,
            $this->urlPropertyName,
        ];
    }

    public function parsedText() : ?string
    {
        return $this->parsed()->text;
    }

    // NewsSourceInterface

    public function largeImage() : ?string
    {
        return $this->parsed()->largeImage();
    }

    public function image() : ?string
    {
        return $this->parsed()->image();
    }

    public function video() : ?string
    {
        return $this->parsed()->video();
    }

    abstract public function displayTitle() : string;

    abstract public function rawText() : ?string;

    public function hasText() : bool
    {
        return strlen($this->rawText()) > 0;
    }

    public function fullText() : ?string
    {
        return $this->getWithProperty(
            $this->fullTextPropertyName
        );
    }

    public function shortText() : ?string
    {
        return $this->getWithProperty(
            $this->shortTextPropertyName
        );
    }

    public function creator() : ?User
    {
        return $this->getWithProperty(
            $this->creatorPropertyName
        );
    }

    public function tagLinks() : TagLinkCollection
    {
        return $this->getWithProperty(
            $this->tagLinksPropertyName
        );
    }

    // LinkableInterface
    // implemented in Linkable trait

    // TaggedInterface
    // implemented in Tagged trait

    // SearchableInterface

    abstract public function code() : string;
}
