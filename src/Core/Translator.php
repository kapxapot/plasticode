<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\TranslatorInterface;

class Translator implements TranslatorInterface
{
    private $dictionaries;

    public function __construct(array $dictionaries)
    {
        $this->dictionaries = $dictionaries;
    }

    public function translate(string $value) : string
    {
        return $this->dictionaries[$value] ?? $value;
    }
}
