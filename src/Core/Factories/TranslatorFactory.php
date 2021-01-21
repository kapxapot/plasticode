<?php

namespace Plasticode\Core\Factories;

use Plasticode\Config\Config;
use Plasticode\Config\Interfaces\LocalizationConfigInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Translator;
use Psr\Container\ContainerInterface;

class TranslatorFactory
{
    public function __invoke(ContainerInterface $container): TranslatorInterface
    {
        /** @var Config */
        $config = $container->get(Config::class);

        /** @var LocalizationConfigInterface */
        $localizationConfig = $container->get(LocalizationConfigInterface::class);

        $lang = $config->viewGlobals()['lang'] ?? 'ru';
        $loc = $localizationConfig->get($lang);

        return new Translator($loc);
    }
}
