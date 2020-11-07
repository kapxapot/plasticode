<?php

namespace Plasticode\Parsing;

use Plasticode\ViewModels\ViewModel;

/**
 * Item for contents list of the parsed text.
 * 
 * @property string|null $label
 * @property string $text
 */
class ContentsItem extends ViewModel
{
    public const LABEL_DELIMITER = '_';

    /** @var string[] */
    protected static array $methodsToExclude = ['displayText'];

    private int $level;
    private ?string $label;
    private string $text;

    /**
     * @param string|null $label For example: "1_1_2".
     * @param string $text Text is supposed to be parsed, can contain html tags.
     */
    public function __construct(int $level, ?string $label, string $text)
    {
        $this->level = $level;
        $this->label = $label;
        $this->text = $text;
    }

    public function level() : int
    {
        return $this->level;
    }

    public function label() : ?string
    {
        return $this->label;
    }

    public function text() : string
    {
        return $this->text;
    }

    private function dottedLabel() : ?string
    {
        return $this->label
            ? str_replace(self::LABEL_DELIMITER, '.', $this->label)
            : null;
    }

    public function displayText() : string
    {
        $label = $this->dottedLabel();
        $prefix = $label ? $label . '. ' : '';

        return $prefix . strip_tags($this->text);
    }
}
