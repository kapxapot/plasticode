<?php

namespace Plasticode\Core;

use Plasticode\Config\Interfaces\LocalizationConfigInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;

/**
 * Translates text strings from one language to another.
 */
class Translator implements TranslatorInterface
{
    private LocalizationConfigInterface $config;
    private string $defaultLangCode;

    public function __construct(
        LocalizationConfigInterface $config,
        string $defaultLangCode
    )
    {
        $this->config = $config;
        $this->defaultLangCode = $defaultLangCode;
    }

    public function translate(string $value, ?string $langCode = null): string
    {
        $dictionary = $this->config->get(
            $langCode ?? $this->defaultLangCode
        );

        return $dictionary[$value] ?? $value;
    }
}
