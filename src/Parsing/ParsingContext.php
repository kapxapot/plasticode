<?php

namespace Plasticode\Parsing;

use Plasticode\Collections\ContentsItemCollection;
use Plasticode\Util\Arrays;
use Plasticode\Util\Text;

class ParsingContext
{
    public ?string $text = null;
    public ContentsItemCollection $contents;
    public ?string $updatedAt = null;

    /** @var string[] */
    public array $largeImages = [];

    /** @var string[] */
    public array $images = [];

    /** @var string[] */
    public array $videos = [];

    private function __construct()
    {
        $this->contents = ContentsItemCollection::empty();
    }

    /**
     * Creates empty context with null text.
     */
    private static function empty() : self
    {
        return new static();
    }

    public static function fromText(?string $text) : self
    {
        $context = self::empty();
        $context->text = $text;

        return $context;
    }

    public static function fromLines(array $lines) : self
    {
        $context = self::empty();
        $context->setLines($lines);

        return $context;
    }

    public static function fromJson(string $json) : self
    {
        $array = @json_decode($json, true);

        $context = self::fromText($array['text']);

        $context->contents = ContentsItemCollection::make(
            array_map(
                fn ($item) => new ContentsItem(...array_values($item)),
                $array['contents'] ?? []
            )
        );

        $context->largeImages = $array['largeImages'] ?? [];
        $context->images = $array['images'] ?? [];
        $context->videos = $array['videos'] ?? [];
        $context->updatedAt = $array['updatedAt'] ?? null;

        return $context;
    }

    public function isEmpty() : bool
    {
        return is_null($this->text);
    }

    public function largeImage() : ?string
    {
        return Arrays::first($this->largeImages);
    }

    public function image() : ?string
    {
        return Arrays::first($this->images);
    }

    public function video() : ?string
    {
        return Arrays::first($this->videos);
    }

    public function addLargeImage(string $url) : void
    {
        $this->largeImages[] = $url;
        $this->addImage($url);
    }

    public function addImage(string $url) : void
    {
        $this->images[] = $url;
    }

    public function addVideo(string $url) : void
    {
        $this->videos[] = $url;
    }

    /**
     * Returns text as an array of lines (strings).
     *
     * @return string[]
     */
    public function getLines() : array
    {
        return Text::toLines($this->text);
    }

    /**
     * Sets text from lines (strings) array.
     *
     * @param string[] $lines
     */
    public function setLines(array $lines) : self
    {
        $lines = Text::trimEmptyLines($lines);
        $this->text = Text::fromLines($lines);

        return $this;
    }
}
