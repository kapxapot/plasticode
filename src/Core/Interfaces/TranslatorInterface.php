<?php

namespace Plasticode\Core\Interfaces;

interface TranslatorInterface
{
    public function translate(string $value, ?string $langCode = null): string;
}
