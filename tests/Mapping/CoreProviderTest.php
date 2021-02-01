<?php

namespace Plasticode\Tests\Mapping;

use Monolog\Logger;
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
use Plasticode\Controllers\AuthController;
use Plasticode\Controllers\CaptchaController;
use Plasticode\Controllers\ParserController;
use Plasticode\Controllers\PasswordController;
use Plasticode\Core\AppContext;
use Plasticode\Core\Cache;
use Plasticode\Core\Env;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Core\Linker;
use Plasticode\Core\Pagination;
use Plasticode\Core\Renderer;
use Plasticode\Core\Session;
use Plasticode\Core\Translator;
use Plasticode\Data\DbMetadata;
use Plasticode\Data\Idiorm\Api;
use Plasticode\Data\Interfaces\ApiInterface;
use Plasticode\Events\EventDispatcher;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Plasticode\Mapping\Providers\CoreProvider;
use Plasticode\Middleware\Factories\AccessMiddlewareFactory;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Repositories\Interfaces\AuthTokenRepositoryInterface;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Twig\TwigView;
use Plasticode\Util\Cases;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouterInterface;

final class CoreProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            Env::class,
            RouterInterface::class,
            ServerRequestInterface::class,
            CutParser::class,
            ParserInterface::class,
            ValidatorInterface::class,

            AuthTokenRepositoryInterface::class,
            MenuRepositoryInterface::class,
            UserRepositoryInterface::class,
        ];
    }

    protected function getProvider(): ?MappingProviderInterface
    {
        return new CoreProvider(
            [
                'root_dir' => '',
                'view' => [
                    'templates_path' => '',
                    'cache_path' => '',
                ]
            ]
        );
    }

    public function testWiring(): void
    {
        // $this->container->withLogger(
        //     (new ConsoleLoggerFactory())()
        // );

        $this->check(Access::class);
        $this->check(AccessMiddlewareFactory::class);
        $this->check(ApiInterface::class, Api::class);
        $this->check(AppContext::class);
        $this->check(AuthInterface::class, Auth::class);
        $this->check(CacheInterface::class, Cache::class);
        $this->check(CaptchaConfigInterface::class, CaptchaConfig::class);
        $this->check(CaptchaInterface::class, Captcha::class);
        $this->check(Cases::class);
        $this->check(Config::class);
        $this->check(DbMetadata::class);
        $this->check(EventDispatcher::class);
        $this->check(LinkerInterface::class, Linker::class);
        $this->check(LoggerInterface::class, Logger::class);
        $this->check(LocalizationConfigInterface::class, LocalizationConfig::class);
        $this->check(Pagination::class);
        $this->check(RendererInterface::class, Renderer::class);
        $this->check(SessionInterface::class, Session::class);
        $this->check(SettingsProviderInterface::class, SettingsProvider::class);
        $this->check(TagsConfigInterface::class, TagsConfig::class);
        $this->check(TranslatorInterface::class, Translator::class);
        $this->check(ViewInterface::class, TwigView::class);

        // controllers

        $this->check(AuthController::class);
        $this->check(CaptchaController::class);
        $this->check(ParserController::class);
        $this->check(PasswordController::class);
    }
}
