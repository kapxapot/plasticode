<?php

namespace Plasticode\Parsing;

use Plasticode\Models\Model;

/**
 * Item for contents list of the parsed text.accordion
 * 
 * @property integer $level
 * @property string|null $label
 * @property string $text
 */
class ContentsItem extends Model
{
    public const LABEL_DELIMITER = '_';

    public function __construct(int $level, ?string $label, string $text)
    {
        $this->level = $level;
        $this->label = $label;
        $this->text = $text;
    }

    public function dottedLabel() : ?string
    {
        return $this->label
            ? str_replace(self::LABEL_DELIMITER, '.', $this->label)
            : null;
    }

    public function displayText() : string
    {
        return
            ($this->dottedLabel() ? $this->dottedLabel() . '. ' : '') .
            strip_tags($this->text);
    }
}
