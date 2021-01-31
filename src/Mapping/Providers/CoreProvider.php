<?php

namespace Plasticode\Mapping\Providers;

use Plasticode\Auth\Access;
use Plasticode\Auth\Auth;
use Plasticode\Auth\Captcha;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Config\CaptchaConfig;
use Plasticode\Config\Config;
use Plasticode\Config\Interfaces\CaptchaConfigInterface;
use Plasticode\Config\Interfaces\LocalizationConfigInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Config\LocalizationConfig;
use Plasticode\Config\TagsConfig;
use Plasticode\Core\Cache;
use Plasticode\Core\Factories\LoggerFactory;
use Plasticode\Core\Factories\SessionFactory;
use Plasticode\Core\Factories\TranslatorFactory;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Core\Linker;
use Plasticode\Core\Renderer;
use Plasticode\Data\Idiorm\Api;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Events\Factories\EventLoggerFactory;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Twig\TwigViewFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class CoreProvider extends MappingProvider
{
    protected array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function getMappings(): array
    {
        return [
            Access::class =>
                fn (Config $config) => new Access(
                    $config->accessSettings()
                ),

            ApiInterface::class => Api::class,
            AuthInterface::class => Auth::class,
            CacheInterface::class => Cache::class,
            CaptchaConfigInterface::class => CaptchaConfig::class,
            CaptchaInterface::class => Captcha::class,

            EventDispatcher::class =>
                fn (ContainerInterface $c, EventLoggerFactory $f) =>
                    (new EventDispatcher())->withLogger(
                        ($f)($c)
                    ),

            LinkerInterface::class => Linker::class,
            LocalizationConfigInterface::class => LocalizationConfig::class,
            LoggerInterface::class => LoggerFactory::class,
            RendererInterface::class => Renderer::class,
            SessionInterface::class => SessionFactory::class,

            SettingsProviderInterface::class =>
                fn () => new SettingsProvider(
                    $this->settings
                ),

            TagsConfigInterface::class => TagsConfig::class,
            TranslatorInterface::class => TranslatorFactory::class,
            ViewInterface::class => TwigViewFactory::class,
        ];
    }
}
