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
use Plasticode\Config\Parsing\BBContainerConfig;
use Plasticode\Config\Parsing\BBParserConfig;
use Plasticode\Config\Parsing\ReplacesConfig;
use Plasticode\Core\AppContext;
use Plasticode\Core\Cache;
use Plasticode\Core\Linker;
use Plasticode\Core\Pagination;
use Plasticode\Core\Renderer;
use Plasticode\Core\Session;
use Plasticode\Core\SettingsProvider;
use Plasticode\Core\Translator;
use Plasticode\Data\DbMetadata;
use Plasticode\Data\Idiorm\Api;
use Plasticode\Events\EventDispatcher;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\External\Gravatar;
use Plasticode\External\Telegram;
use Plasticode\External\Twitch;
use Plasticode\External\Twitter;
use Plasticode\Generators\GeneratorContext;
use Plasticode\Generators\GeneratorResolver;
use Plasticode\Generators\MenuGenerator;
use Plasticode\Generators\MenuItemGenerator;
use Plasticode\Generators\RoleGenerator;
use Plasticode\Generators\UserGenerator;
use Plasticode\Handlers\ErrorHandler;
use Plasticode\Handlers\NotAllowedHandler;
use Plasticode\Handlers\NotFoundHandler;
use Plasticode\Hydrators\AuthTokenHydrator;
use Plasticode\Hydrators\MenuHydrator;
use Plasticode\Hydrators\MenuItemHydrator;
use Plasticode\Hydrators\TagHydrator;
use Plasticode\Hydrators\UserHydrator;
use Plasticode\IO\File;
use Plasticode\IO\Image;
use Plasticode\Models\Validation\PasswordValidation;
use Plasticode\Models\Validation\UserValidation;
use Plasticode\ObjectProxy;
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
use Plasticode\Repositories\Idiorm\AuthTokenRepository;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;
use Plasticode\Repositories\Idiorm\MenuItemRepository;
use Plasticode\Repositories\Idiorm\MenuRepository;
use Plasticode\Repositories\Idiorm\RoleRepository;
use Plasticode\Repositories\Idiorm\TagRepository;
use Plasticode\Repositories\Idiorm\UserRepository;
use Plasticode\Services\AuthService;
use Plasticode\Twig\Extensions\AccessRightsExtension;
use Plasticode\Twig\Extensions\TranslatorExtension;
use Plasticode\Twig\TwigView;
use Plasticode\Util\Cases;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig\Extension\DebugExtension;

class Bootstrap
{
    protected array $settings;

    /**
     * Current directory
     */
    protected string $dir;

    public function __construct(array $settings, string $dir)
    {
        $this->settings = $settings;
        $this->dir = $dir;
    }

    /**
     * - Fill container.
     * - Init database.
     * - Register event handlers.
     */
    public function boot(ContainerInterface $container) : ContainerInterface
    {
        foreach ($this->getMappings() as $key => $value) {
            $container[$key] = $value;
        }

        $this->initDatabase();
        $this->registerEventHandlers($container);

        return $container;
    }

    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
    {
        $map = [];

        $map['cache'] = fn (ContainerInterface $c) =>
            new Cache();

        $map['access'] = fn (ContainerInterface $c) =>
            new Access(
                $this->settings['access']
            );

        $map['settingsProvider'] = fn (ContainerInterface $c) =>
            new SettingsProvider(
                $this->settings
            );

        $map['dbMetadata'] = fn (ContainerInterface $c) =>
            new DbMetadata(
                $c->settingsProvider
            );

        $map['auth'] = fn (ContainerInterface $c) =>
            new Auth(
                $c->session
            );

        $map['session'] = function (ContainerInterface $c) {
            $root = $this->settings['root'];
            $name = 'sessionContainer' . $root;
            
            return new Session($name);
        };

        $map['logger'] = function (ContainerInterface $c) {
            $logger = new Logger(
                $this->settings['logger']['name']
            );

            $logger->pushProcessor(
                function ($record) use ($c) {
                    $user = $c->auth->getUser();

                    if ($user) {
                        $record['extra']['user'] = $user->toString();
                    }

                    $token = $c->auth->getToken();

                    if ($token) {
                        $record['extra']['token'] = $token->toString();
                    }

                    return $record;
                }
            );

            $path = $this->settings['logger']['path'];
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

        $map['captchaConfig'] = fn (ContainerInterface $c) =>
            new CaptchaConfig();

        $map['captcha'] = fn (ContainerInterface $c) =>
            new Captcha(
                $c->session,
                $c->captchaConfig
            );

        $map['generatorContext'] = fn (ContainerInterface $c) =>
            new GeneratorContext(
                $c->settingsProvider,
                $c->router,
                $c->api,
                $c->validator,
                $c->validationRules
            );

        $map['generatorResolver'] = fn (ContainerInterface $c) =>
            new GeneratorResolver(
                $c
            );

        $map['menusGenerator'] = fn (ContainerInterface $c) =>
            new MenuGenerator(
                $c->generatorContext,
                $c->menuRepository
            );

        $map['menuItemsGenerator'] = fn (ContainerInterface $c) =>
            new MenuItemGenerator(
                $c->generatorContext,
                $c->menuRepository,
                $c->menuItemRepository
            );

        $map['rolesGenerator'] = fn (ContainerInterface $c) =>
            new RoleGenerator(
                $c->generatorContext,
                $c->roleRepository
            );

        $map['usersGenerator'] = fn (ContainerInterface $c) =>
            new UserGenerator(
                $c->generatorContext,
                $c->userRepository,
                $c->userValidation
            );

        $map['cases'] = fn (ContainerInterface $c) =>
            new Cases();

        $map['repositoryContext'] = fn (ContainerInterface $c) =>
            new RepositoryContext(
                $c->access,
                $c->auth,
                $c->cache,
                $c->dbMetadata
            );

        $map['authTokenRepository'] = fn (ContainerInterface $c) =>
            new AuthTokenRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new AuthTokenHydrator(
                        $c->userRepository
                    )
                )
            );

        $map['menuItemRepository'] = fn (ContainerInterface $c) =>
            new MenuItemRepository(
                $c->repositoryContext,
                new MenuItemHydrator(
                    $c->linker
                )
            );

        $map['menuRepository'] = fn (ContainerInterface $c) =>
            new MenuRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new MenuHydrator(
                        $c->menuItemRepository,
                        $c->linker
                    )
                )
            );

        $map['roleRepository'] = fn (ContainerInterface $c) =>
            new RoleRepository(
                $c->repositoryContext
            );

        $map['tagRepository'] = fn (ContainerInterface $c) =>
            new TagRepository(
                $c->repositoryContext,
                new TagHydrator(
                    $c->linker
                )
            );

        $map['userRepository'] = fn (ContainerInterface $c) =>
            new UserRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new UserHydrator(
                        $c->roleRepository,
                        $c->linker,
                        $c->gravatar
                    )
                )
            );

        $map['appContext'] = fn (ContainerInterface $c) =>
            new AppContext(
                $c->settingsProvider,
                $c->translator,
                $c->validator,
                $c->view,
                $c->logger,
                $c->menuRepository
            );

        $map['view'] = function (ContainerInterface $c) {
            $tws = $this->settings['view'];

            $path = $tws['templates_path'];
            $path = is_array($path) ? $path : [$path];

            $templatesPath = array_map(
                function ($tPath) {
                    return File::combine($this->dir, $tPath);
                },
                $path
            );

            $cachePath = $tws['cache_path'];

            if ($cachePath) {
                $cachePath = File::combine($this->dir, $cachePath);
            }

            $view = new Twig(
                $templatesPath,
                [
                    'cache' => $cachePath,
                    'debug' => $this->settings['debug'],
                ]
            );

            $view->addExtension(
                new TwigExtension(
                    $c->router,
                    $c->request->getUri()
                )
            );

            $view->addExtension(
                new AccessRightsExtension(
                    $c->access,
                    $c->auth
                )
            );

            $view->addExtension(
                new TranslatorExtension(
                    $c->translator
                )
            );

            $view->addExtension(new DebugExtension());

            // set globals
            $globals = $this->settings['view_globals'];
            foreach ($globals as $key => $value) {
                $view[$key] = $value;
            }

            $user = $c->auth->getUser();

            $view['auth'] = [
                'check' => $c->authService->check(),
                'user' => $user,
                'role' => $c->auth->getRole(),
                'avatar' => is_null($user)
                    ? $c->linker->defaultGravatarUrl()
                    : $user->gravatarUrl(),
            ];

            $view['image_types'] = Image::buildTypesString();

            $view['tables'] = $this->settings['tables'];
            $view['entities'] = $this->settings['entities'];

            $view['root'] = $this->settings['root'];
            $view['api'] = $this->settings['api'];

            if (isset($this->settings['auth_token_key'])) {
                $view['auth_token_key'] = $this->settings['auth_token_key'];
            }

            /** @var Twig $view */
            return new TwigView($view);
        };

        $map['localizationConfig'] = fn (ContainerInterface $c) =>
            new LocalizationConfig();

        $map['translator'] = function (ContainerInterface $c) {
            $lang = $this->settings['view_globals']['lang'] ?? 'ru';
            $loc = $c->localizationConfig->get($lang);
            
            return new Translator($loc);
        };

        $map['validator'] = fn (ContainerInterface $c) =>
            new Validator(
                $c->translator
            );

        $map['validationRules'] = fn (ContainerInterface $c) =>
            new ValidationRules(
                $c->settingsProvider
            );

        $map['passwordValidation'] = fn (ContainerInterface $c) =>
            new PasswordValidation(
                $c->validationRules
            );

        $map['userValidation'] = fn (ContainerInterface $c) =>
            new UserValidation(
                $c->validationRules,
                $c->userRepository
            );

        $map['api'] = fn (ContainerInterface $c) =>
            new Api(
                $c->access,
                $c->auth,
                $c->dbMetadata,
                $c->logger,
                $c->userRepository
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

        $map['tagsConfig'] = fn (ContainerInterface $c) =>
            new TagsConfig();

        $map['linker'] = fn (ContainerInterface $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router,
                $c->tagsConfig
            );

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

        $map['parser'] = fn (ContainerInterface $c) =>
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

        $map['cutParser'] = fn (ContainerInterface $c) =>
            new CutParser($c->cleanupParser);

        $map['eventDispatcher'] = fn (ContainerInterface $c) =>
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

        $map['authService'] = fn (ContainerInterface $c) =>
            new AuthService(
                $c->auth,
                $c->settingsProvider,
                $c->authTokenRepository,
                $c->userRepository
            );

        // external

        $map['gravatar'] = fn (ContainerInterface $c) =>
            new Gravatar();
        
        $map['twitch'] = fn (ContainerInterface $c) =>
            new Twitch(
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

        // handlers

        $map['notFoundHandler'] = fn (ContainerInterface $c) =>
            new NotFoundHandler(
                $c->appContext
            );

        $map['errorHandler'] = fn (ContainerInterface $c) =>
            new ErrorHandler(
                $c->appContext
            );

        $map['notAllowedHandler'] = fn (ContainerInterface $c) =>
            new NotAllowedHandler(
                $c->appContext
            );

        return $map;
    }

    public function initDatabase() : void
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

    public function registerEventHandlers(ContainerInterface $c) : void
    {
    }
}
