<?php

namespace Plasticode\Core\Factories;

use Plasticode\Config\Config;
use Plasticode\Config\Interfaces\LocalizationConfigInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Translator;

class TranslatorFactory
{
    public function __invoke(
        Config $config,
        LocalizationConfigInterface $localizationConfig
    ): TranslatorInterface
    {
        return new Translator($localizationConfig, $config->langCode());
    }
}
