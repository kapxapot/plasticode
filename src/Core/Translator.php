<?php

namespace Plasticode\Core;

class Translator
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
