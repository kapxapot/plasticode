<?php

namespace Plasticode\Config\MappingProviders;

use Plasticode\Auth\Access;
use Plasticode\Auth\Auth;
use Plasticode\Auth\Captcha;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Config\CaptchaConfig;
use Plasticode\Config\Config;
use Plasticode\Config\Interfaces\CaptchaConfigInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Config\LocalizationConfig;
use Plasticode\Config\TagsConfig;
use Plasticode\Controllers\Auth\AuthController;
use Plasticode\Controllers\Factories\AuthControllerFactory;
use Plasticode\Controllers\Factories\ParserControllerFactory;
use Plasticode\Controllers\ParserController;
use Plasticode\Core\AppContext;
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
use Plasticode\Core\Pagination;
use Plasticode\Core\Renderer;
use Plasticode\Data\DbMetadata;
use Plasticode\Data\Idiorm\Api;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Events\Factories\EventLoggerFactory;
use Plasticode\External\Gravatar;
use Plasticode\External\Telegram;
use Plasticode\External\Twitch;
use Plasticode\External\Twitter;
use Plasticode\Interfaces\MappingProviderInterface;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Services\AuthService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Twig\TwigViewFactory;
use Plasticode\Util\Cases;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouterInterface;

class CoreProvider implements MappingProviderInterface
{
    protected array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    public function getMappings(): array
    {
        return [
            SettingsProviderInterface::class =>
                fn (ContainerInterface $c) => new SettingsProvider(
                    $this->settings
                ),

            Config::class =>
                fn (ContainerInterface $c) => new Config(
                    $c->get(SettingsProviderInterface::class)
                ),

            CacheInterface::class => fn (ContainerInterface $c) => new Cache(),

            Access::class =>
                fn (ContainerInterface $c) => new Access(
                    $c->get(Config::class)
                ),

            DbMetadata::class =>
                fn (ContainerInterface $c) => new DbMetadata(
                    $c->get(Config::class)
                ),

            LoggerInterface::class => LoggerFactory::class,
            SessionInterface::class => SessionFactory::class,
            ViewInterface::class => TwigViewFactory::class,
            TranslatorInterface::class => TranslatorFactory::class,

            RendererInterface::class =>
                fn (ContainerInterface $c) => new Renderer(
                    $c->get(ViewInterface::class)
                ),

            AuthInterface::class =>
                fn (ContainerInterface $c) => new Auth(
                    $c->get(SessionInterface::class)
                ),

            TagsConfigInterface::class =>
                fn (ContainerInterface $c) => new TagsConfig(),

            LinkerInterface::class =>
                fn (ContainerInterface $c) => new Linker(
                    $c->get(SettingsProviderInterface::class),
                    $c->get(RouterInterface::class),
                    $c->get(TagsConfigInterface::class)
                ),

            AppContext::class =>
                fn (ContainerInterface $c) => new AppContext(
                    $c->get(SettingsProviderInterface::class),
                    $c->get(TranslatorInterface::class),
                    $c->get(ValidatorInterface::class),
                    $c->get(ViewInterface::class),
                    $c->get(LoggerInterface::class),
                    $c->get(MenuRepositoryInterface::class)
                ),

            CaptchaConfigInterface::class =>
                fn (ContainerInterface $c) => new CaptchaConfig(),

            CaptchaInterface::class =>
                fn (ContainerInterface $c) => new Captcha(
                    $c->get(SessionInterface::class),
                    $c->get(CaptchaConfigInterface::class)
                ),

            Cases::class => fn (ContainerInterface $c) => new Cases(),

            LocalizationConfig::class =>
                fn (ContainerInterface $c) => new LocalizationConfig(),

            ApiInterface::class =>
                fn (ContainerInterface $c) => new Api(
                    $c->get(Access::class),
                    $c->get(AuthInterface::class),
                    $c->get(DbMetadata::class),
                    $c->get(LoggerInterface::class),
                    $c->get(UserRepositoryInterface::class)
                ),

            Pagination::class =>
                fn (ContainerInterface $c) => new Pagination(
                    $c->get(LinkerInterface::class),
                    $c->get(RendererInterface::class)
                ),

            // todo: DI for duplicate interface instances (logger + eventLogger)
            'eventLogger' => EventLoggerFactory::class,

            EventDispatcher::class =>
                fn (ContainerInterface $c) => new EventDispatcher(
                    fn (string $msg) => $c->get('eventLogger')->info($msg)
                ),

            // services

            AuthService::class =>
                fn (ContainerInterface $c) => new AuthService(
                    $c->get(AuthInterface::class),
                    $c->get(SettingsProviderInterface::class),
                    $c->get(AuthTokenRepositoryInterface::class),
                    $c->get(UserRepositoryInterface::class)
                ),

            // controller factories

            AuthController::class => AuthControllerFactory::class,
            ParserController::class => ParserControllerFactory::class,

            // middleware factories

            AccessMiddlewareFactory::class =>
                fn (ContainerInterface $c) => new AccessMiddlewareFactory(
                    $c->get(Access::class),
                    $c->get(AuthInterface::class),
                    $c->get(RouterInterface::class)
                ),

            // external

            Gravatar::class => fn (ContainerInterface $c) => new Gravatar(),

            Twitch::class =>
                fn (ContainerInterface $c) => new Twitch(
                    $c->get(SettingsProviderInterface::class)->get('twitch')
                ),

            Telegram::class =>
                fn (ContainerInterface $c) => new Telegram(
                    $c->get(SettingsProviderInterface::class)->get('telegram')
                ),

            Twitter::class =>
                fn (ContainerInterface $c) => new Twitter(
                    $c->get(SettingsProviderInterface::class)->get('twitter')
                ),
        ];
    }
}
