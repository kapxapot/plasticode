<?php

namespace Plasticode\Config\Interfaces;

interface LocalizationConfigInterface
{
    /**
     * Returns localization config for the given language.
     *
     * Base language - English ("en").
     *
     * Define dictionaries for languages as functions named by language code.
     * E.g., for Russian ("ru"):
     *
     * <code>
     *     protected function ru(): array
     * </code>
     *
     * @return array<string, string>
     */
    function get(?string $lang): array;
}
