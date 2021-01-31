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
        $lang = $config->viewGlobals()['lang'] ?? 'ru';
        $loc = $localizationConfig->get($lang);

        return new Translator($loc);
    }
}
