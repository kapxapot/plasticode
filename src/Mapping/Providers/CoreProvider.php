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
use Plasticode\Controllers\Factories\AuthControllerFactory;
use Plasticode\Controllers\Factories\ParserControllerFactory;
use Plasticode\Controllers\Factories\PasswordControllerFactory;
use Plasticode\Controllers\AuthController;
use Plasticode\Controllers\CaptchaController;
use Plasticode\Controllers\Factories\CaptchaControllerFactory;
use Plasticode\Controllers\ParserController;
use Plasticode\Controllers\PasswordController;
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
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Twig\TwigViewFactory;
use Plasticode\Util\Cases;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouterInterface;

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
                    $c->get(Config::class)->accessSettings()
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

            LocalizationConfigInterface::class =>
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

            // controller factories

            AuthController::class => AuthControllerFactory::class,
            CaptchaController::class => CaptchaControllerFactory::class,
            ParserController::class => ParserControllerFactory::class,
            PasswordController::class => PasswordControllerFactory::class,

            // middleware factories

            AccessMiddlewareFactory::class =>
                fn (ContainerInterface $c) => new AccessMiddlewareFactory(
                    $c->get(Access::class),
                    $c->get(AuthInterface::class),
                    $c->get(RouterInterface::class)
                ),
        ];
    }
}
