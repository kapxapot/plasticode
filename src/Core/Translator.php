<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\TranslatorInterface;

/**
 * Translates text strings from one language to another.
 */
class Translator implements TranslatorInterface
{
    /** @var array<string, string> */
    private array $dictionary;

    public function __construct(?array $dictionary = null)
    {
        $this->dictionary = $dictionary ?? [];
    }

    public function translate(string $value) : string
    {
        return $this->dictionary[$value] ?? $value;
    }
}
