<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

/**
 * Parses [cut] tag in already parsed text,
 * removing it in full mode and cutting the text in short mode.
 */
class CutParser
{
    private CleanupParser $cleanupParser;
    private string $tag = '[cut]';

    public function __construct(CleanupParser $cleanupParser)
    {
        $this->cleanupParser = $cleanupParser;
    }

    /**
     * @return $this
     */
    public function withTag(string $tag): self
    {
        Assert::stringNotEmpty($tag);

        $this->tag = $tag;

        return $this;
    }

    /**
     * Just removes the [cut] tag.
     */
    public function full(?string $text): ?string
    {
        $cut = $this->tag;
        $cutpos = strpos($text, $cut);

        if ($cutpos === false) {
            return $text;
        }

        $text = str_replace($cut, '', $text);

        return $this->cleanup($text);
    }

    /**
     * Cuts the already parsed text by [cut] tag.
     * If there's no [cut] tag, returns null (!).
     */
    public function short(?string $text): ?string
    {
        $cut = $this->tag;
        $cutpos = strpos($text, $cut);

        if ($cutpos === false) {
            return null;
        }
        
        $text = substr($text, 0, $cutpos);
        $text = Text::trimNewLinesAndBrs($text);

        return $this->cleanup($text);
    }

    private function cleanup(?string $text): ?string
    {
        $context = $this->cleanupParser->parse($text);

        return $context->text;
    }
}
