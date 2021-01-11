<?php

namespace Plasticode\Config;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use ORM;
use PDO;
use Plasticode\Auth\Access;
use Plasticode\Auth\Auth;
use Plasticode\Auth\Captcha;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Config\Interfaces\CaptchaConfigInterface;
use Plasticode\Config\Interfaces\TagsConfigInterface;
use Plasticode\Config\Parsing\BBContainerConfig;
use Plasticode\Config\Parsing\BBParserConfig;
use Plasticode\Config\Parsing\ReplacesConfig;
use Plasticode\Controllers\Auth\AuthController;
use Plasticode\Controllers\Factories\AuthControllerFactory;
use Plasticode\Controllers\Factories\ParserControllerFactory;
use Plasticode\Core\AppContext;
use Plasticode\Core\Cache;
use Plasticode\Core\Factories\LoggerFactory;
use Plasticode\Core\Factories\SessionFactory;
use Plasticode\Core\Factories\TranslatorFactory;
use Plasticode\Core\Factories\ViewFactory;
use Plasticode\Core\Interfaces\CacheInterface;
use Plasticode\Core\Interfaces\LinkerInterface;
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
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\External\Gravatar;
use Plasticode\External\Telegram;
use Plasticode\External\Twitch;
use Plasticode\External\Twitter;
use Plasticode\Handlers\ErrorHandler;
use Plasticode\Handlers\NotAllowedHandler;
use Plasticode\Handlers\NotFoundHandler;
use Plasticode\Interfaces\MappingProviderInterface;
use Plasticode\IO\File;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\LinkMapperSource;
use Plasticode\Parsing\Parsers\BB\BBParser;
use Plasticode\Parsing\Parsers\BB\Container\BBContainerParser;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeRenderer;
use Plasticode\Parsing\Parsers\CleanupParser;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Parsing\Parsers\DoubleBracketsParser;
use Plasticode\Parsing\Parsers\MarkdownParser;
use Plasticode\Parsing\Steps\NewLinesToBrsStep;
use Plasticode\Parsing\Steps\ReplacesStep;
use Plasticode\Parsing\Steps\TitlesStep;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;
use Plasticode\Services\AuthService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;
use Plasticode\Util\Cases;
use Plasticode\Validation\Interfaces\ValidatorInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Slim\Interfaces\RouterInterface;

class Bootstrap implements MappingProviderInterface
{
    protected array $settings;

    /** @var MappingProviderInterface[] */
    protected array $mappingProviders = [];

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Registers mapping providers.
     * 
     * @return $this
     */
    public function register(MappingProviderInterface ...$mappingProviders): self
    {
        $this->mappingProviders = array_merge(
            $this->mappingProviders,
            $mappingProviders
        );

        return $this;
    }

    /**
     * - Fill container.
     * - Init database.
     * - Register event handlers.
     */
    public function boot(ContainerInterface $container): ContainerInterface
    {
        foreach ($this->aggregateMappings() as $key => $value) {
            $container[$key] = $value;
        }

        $this->initDatabase($container);

        $this->registerGenerators($container);
        $this->registerEventHandlers($container);

        return $container;
    }

    /**
     * Aggregates the own mappings with all mappings from providers.
     */
    private function aggregateMappings(): array
    {
        $mappings = $this->getMappings();

        foreach ($this->mappingsProviders as $provider) {
            $mappings = array_merge(
                $mappings,
                $provider->getMappings()
            );
        }

        return $mappings;
    }

    /**
     * Get mappings for DI container.
     */
    public function getMappings(): array
    {
        $map = [];

        // aliases

        $map[RouterInterface::class] = fn (ContainerInterface $c) => $c->get('router');

        // core

        $map[SettingsProviderInterface::class] =
            fn (ContainerInterface $c) => new SettingsProvider(
                $this->settings
            );

        $map[Config::class] =
            fn (ContainerInterface $c) => new Config(
                $c->get(SettingsProviderInterface::class)
            );

        $map[CacheInterface::class] = fn (ContainerInterface $c) => new Cache();

        $map[Access::class] =
            fn (ContainerInterface $c) => new Access(
                $c->get(Config::class)
            );

        $map[DbMetadata::class] =
            fn (ContainerInterface $c) => new DbMetadata(
                $c->get(Config::class)
            );

        $map[AuthInterface::class] =
            fn (ContainerInterface $c) => new Auth(
                $c->get(SessionInterface::class)
            );

        $map[LoggerInterface::class] = LoggerFactory::class;
        $map[SessionInterface::class] = SessionFactory::class;
        $map[ViewInterface::class] = ViewFactory::class;
        $map[TranslatorInterface::class] = TranslatorFactory::class;

        $map[LinkerInterface::class] =
            fn (ContainerInterface $c) => new Linker(
                $c->get(SettingsProviderInterface::class),
                $c->get(RouterInterface::class),
                $c->get(TagsConfigInterface::class)
            );

        $map[AppContext::class] =
            fn (ContainerInterface $c) => new AppContext(
                $c->get(SettingsProviderInterface::class),
                $c->get(TranslatorInterface::class),
                $c->get(ValidatorInterface::class),
                $c->get(ViewInterface::class),
                $c->get(LoggerInterface::class),
                $c->get(MenuRepositoryInterface::class)
            );

        // captcha

        $map[CaptchaConfigInterface::class] =
            fn (ContainerInterface $c) => new CaptchaConfig();

        $map[CaptchaInterface::class] =
            fn (ContainerInterface $c) => new Captcha(
                $c->get(SessionInterface::class),
                $c->get(CaptchaConfigInterface::class)
            );

        $map[Cases::class] = fn (ContainerInterface $c) => new Cases();

        $map[LocalizationConfig::class] =
            fn (ContainerInterface $c) => new LocalizationConfig();

        $map[ApiInterface::class] =
            fn (ContainerInterface $c) => new Api(
                $c->get(Access::class),
                $c->get(AuthInterface::class),
                $c->get(DbMetadata::class),
                $c->get(LoggerInterface::class),
                $c->get(UserRepositoryInterface::class)
            );

        $map['renderer'] = fn (ContainerInterface $c) =>
            new Renderer(
                $c->view
            );

        $map['pagination'] = fn (ContainerInterface $c) =>
            new Pagination(
                $c->linker,
                $c->renderer
            );

        $map[TagsConfigInterface::class] =
            fn (ContainerInterface $c) => new TagsConfig();

        $map['replacesConfig'] = fn (ContainerInterface $c) =>
            new ReplacesConfig();

        $map['cleanupParser'] = fn (ContainerInterface $c) =>
            new CleanupParser(
                $c->replacesConfig
            );

        $map['bbParserConfig'] = fn (ContainerInterface $c) =>
            new BBParserConfig(
                $c->linker
            );

        $map['bbParser'] = fn (ContainerInterface $c) =>
            new BBParser(
                $c->bbParserConfig,
                $c->renderer
            );

        // no double brackets link mappers by default
        // add them!
        $map['doubleBracketsConfig'] = fn (ContainerInterface $c) =>
            new LinkMapperSource();

        $map['doubleBracketsParser'] = fn (ContainerInterface $c) =>
            new DoubleBracketsParser(
                $c->doubleBracketsConfig
            );

        $map['lineParser'] = fn (ContainerInterface $c) =>
            new CompositeParser(
                $c->bbParser,
                $c->doubleBracketsParser
            );

        $map['bbContainerConfig'] = fn (ContainerInterface $c) =>
            new BBContainerConfig();

        $map['bbContainerParser'] = fn (ContainerInterface $c) =>
            new BBContainerParser(
                $c->bbContainerConfig,
                new BBSequencer(),
                new BBTreeBuilder(),
                new BBTreeRenderer($c->renderer)
            );

        $map[ParserInterface::class] = fn (ContainerInterface $c) =>
            new CompositeParser(
                new TitlesStep($c->renderer, $c->lineParser),
                new MarkdownParser($c->renderer),
                new NewLinesToBrsStep(),
                $c->bbContainerParser,
                $c->bbParser,
                new ReplacesStep($c->replacesConfig),
                $c->doubleBracketsParser,
                $c->cleanupParser
            );

        $map[CutParser::class] = fn (ContainerInterface $c) =>
            new CutParser($c->cleanupParser);

        $map[EventDispatcher::class] = fn (ContainerInterface $c) =>
            new EventDispatcher(
                fn (string $msg) => $c->eventLog->info($msg)
            );

        $map['eventLog'] = function (ContainerInterface $c) {
            $logger = new Logger(
                $this->settings['event_log']['name']
            );

            $path = $this->settings['event_log']['path'];
            $path = File::absolutePath($this->dir, $path);

            $handler = new StreamHandler(
                $path ?? '',
                $path ? Logger::DEBUG : 999
            );

            $formatter = new LineFormatter(
                null, null, false, true
            );

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        };

        // services

        $map[AuthService::class] = fn (ContainerInterface $c) =>
            new AuthService(
                $c->auth,
                $c->get(SettingsProviderInterface::class),
                $c->authTokenRepository,
                $c->userRepository
            );

        // factories

        $map = array_merge(
            $map,
            [
                AuthController::class => AuthControllerFactory::class,
                ParserController::class => ParserControllerFactory::class,
            ]
        );

        // external

        $map[Gravatar::class] =
            fn (ContainerInterface $c) => new Gravatar();
        
        $map['twitch'] =
            fn (ContainerInterface $c) => new Twitch(
                $this->settings['twitch']
            );

        $map['telegram'] = fn (ContainerInterface $c) =>
            new Telegram(
                $this->settings['telegram']
            );

        $map['twitter'] = fn (ContainerInterface $c) =>
            new Twitter(
                $this->settings['twitter']
            );

        // handlers (Slim)

        $map['notFoundHandler'] =
            fn (ContainerInterface $c) => new NotFoundHandler(
                $c->get(AppContext::class)
            );

        $map['errorHandler'] =
            fn (ContainerInterface $c) => new ErrorHandler(
                $c->get(AppContext::class)
            );

        $map['notAllowedHandler'] =
            fn (ContainerInterface $c) => new NotAllowedHandler(
                $c->get(AppContext::class)
            );

        return $map;
    }

    public function initDatabase(ContainerInterface $c) : void
    {
        $dbs = $this->settings['db'];

        $adapter = $dbs['adapter'] ?? null;

        if ($adapter !== 'mysql') {
            throw new InvalidConfigurationException(
                'The only supported DB adapter is MySQL, sorry.'
            );
        }

        // init Idiorm

        ORM::configure(
            'mysql:host=' . $dbs['host'] . ';dbname=' . $dbs['database']
        );

        $config = [
            'username' => $dbs['user'],
            'password' => $dbs['password'] ?? '',
            'driver_options' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
            ]
        ];

        $port = $dbs['port'] ?? null;

        if ($port > 0) {
            $config['port'] = $port;
        }

        ORM::configure($config);
    }

    protected function registerGenerators(ContainerInterface $c): void
    {
    }

    protected function registerEventHandlers(ContainerInterface $c): void
    {
    }
}
