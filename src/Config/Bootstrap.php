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
use Plasticode\Data\Api;
use Plasticode\Data\Db;
use Plasticode\Events\EventDispatcher;
use Plasticode\Exceptions\InvalidConfigurationException;
use Plasticode\External\Gravatar;
use Plasticode\External\Telegram;
use Plasticode\External\Twitch;
use Plasticode\External\Twitter;
use Plasticode\Generators\GeneratorResolver;
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
use Slim\Collection as SlimCollection;
use Slim\Container;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig\Extension\DebugExtension;

class Bootstrap
{
    protected SlimCollection $settings;

    /**
     * Database settings
     */
    protected array $dbSettings;

    /**
     * Current directory
     */
    protected string $dir;

    public function __construct(SlimCollection $settings, string $dir)
    {
        $this->settings = $settings;
        $this->dbSettings = $this->settings['db'];
        $this->dir = $dir;
    }

    public function fillContainer(Container $container) : Container
    {
        foreach ($this->getMappings() as $key => $value) {
            $container[$key] = $value;
        }

        return $container;
    }

    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
    {
        $map = [];

        $map['cache'] = fn (Container $c) =>
            new Cache();

        $map['access'] = fn (Container $c) =>
            new Access(
                $this->settings['access']
            );

        $map['settingsProvider'] = fn (Container $c) =>
            new SettingsProvider(
                $c->get('settings')
            );

        $map['db'] = function (Container $c) {
            $dbs = $this->dbSettings;

            $adapter = $dbs['adapter'] ?? null;

            if ($adapter !== 'mysql') {
                throw new InvalidConfigurationException(
                    'The only supported DB adapter is MySQL, sorry.'
                );
            }

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

            return new Db(
                $c->settingsProvider
            );
        };

        $map['auth'] = fn (Container $c) =>
            new Auth(
                $c->session
            );

        $map['session'] = function (Container $c) {
            $root = $this->settings['root'];
            $name = 'sessionContainer' . $root;
            
            return new Session($name);
        };

        $map['logger'] = function (Container $c) {
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

        $map['captchaConfig'] = fn (Container $c) =>
            new CaptchaConfig();

        $map['captcha'] = fn (Container $c) =>
            new Captcha(
                $c->session,
                $c->captchaConfig
            );

        $map['generatorResolver'] = fn (Container $c) =>
            new GeneratorResolver(
                $c,
                ['\\App\\Generators']
            );

        $map['cases'] = fn (Container $c) =>
            new Cases();

        $map['repositoryContext'] = fn (Container $c) =>
            new RepositoryContext(
                $c->access,
                $c->auth,
                $c->cache,
                $c->db
            );

        $map['authTokenRepository'] = fn (Container $c) =>
            new AuthTokenRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new AuthTokenHydrator(
                        $c->userRepository
                    )
                )
            );

        $map['menuItemRepository'] = fn (Container $c) =>
            new MenuItemRepository(
                $c->repositoryContext,
                new MenuItemHydrator(
                    $c->linker
                )
            );

        $map['menuRepository'] = fn (Container $c) =>
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

        $map['roleRepository'] = fn (Container $c) =>
            new RoleRepository(
                $c->repositoryContext
            );

        $map['tagRepository'] = fn (Container $c) =>
            new TagRepository(
                $c->repositoryContext,
                new TagHydrator(
                    $c->linker
                )
            );

        $map['userRepository'] = fn (Container $c) =>
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

        $map['appContext'] = fn (Container $c) =>
            new AppContext(
                $c->settingsProvider,
                $c->translator,
                $c->validator,
                $c->view,
                $c->logger,
                $c->menuRepository
            );

        $map['view'] = function (Container $c) {
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

        $map['localizationConfig'] = fn (Container $c) =>
            new LocalizationConfig();

        $map['translator'] = function (Container $c) {
            $lang = $this->settings['view_globals']['lang'] ?? 'ru';
            $loc = $c->localizationConfig->get($lang);
            
            return new Translator($loc);
        };

        $map['validator'] = fn (Container $c) =>
            new Validator(
                $c->translator
            );

        $map['validationRules'] = fn (Container $c) =>
            new ValidationRules(
                $c->settingsProvider
            );

        $map['passwordValidation'] = fn (Container $c) =>
            new PasswordValidation(
                $c->validationRules
            );

        $map['userValidation'] = fn (Container $c) =>
            new UserValidation(
                $c->validationRules,
                $c->userRepository
            );

        $map['api'] = fn (Container $c) =>
            new Api(
                $c->access,
                $c->auth,
                $c->db,
                $c->logger,
                $c->userRepository
            );

        $map['renderer'] = fn (Container $c) =>
            new Renderer(
                $c->view
            );

        $map['pagination'] = fn (Container $c) =>
            new Pagination(
                $c->linker,
                $c->renderer
            );

        $map['tagsConfig'] = fn (Container $c) =>
            new TagsConfig();

        $map['linker'] = fn (Container $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router,
                $c->tagsConfig
            );

        $map['replacesConfig'] = fn (Container $c) =>
            new ReplacesConfig();

        $map['cleanupParser'] = fn (Container $c) =>
            new CleanupParser(
                $c->replacesConfig
            );

        $map['bbParserConfig'] = fn (Container $c) =>
            new BBParserConfig(
                $c->linker
            );

        $map['bbParser'] = fn (Container $c) =>
            new BBParser(
                $c->bbParserConfig,
                $c->renderer
            );

        // no double brackets link mappers by default
        // add them!
        $map['doubleBracketsConfig'] = fn (Container $c) =>
            new LinkMapperSource();

        $map['doubleBracketsParser'] = fn (Container $c) =>
            new DoubleBracketsParser(
                $c->doubleBracketsConfig
            );

        $map['lineParser'] = fn (Container $c) =>
            new CompositeParser(
                $c->bbParser,
                $c->doubleBracketsParser
            );

        $map['bbContainerConfig'] = fn (Container $c) =>
            new BBContainerConfig();

        $map['bbContainerParser'] = fn (Container $c) =>
            new BBContainerParser(
                $c->bbContainerConfig,
                new BBSequencer(),
                new BBTreeBuilder(),
                new BBTreeRenderer($c->renderer)
            );

        $map['parser'] = fn (Container $c) =>
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

        $map['cutParser'] = fn (Container $c) =>
            new CutParser($c->cleanupParser);

        $map['eventDispatcher'] = fn (Container $c) =>
            new EventDispatcher(
                $c->eventHandlers,
                fn (string $msg) => $c->eventLog->info($msg)
            );

        $map['eventHandlers'] = fn (Container $c) => [];

        $map['eventLog'] = function (Container $c) {
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

        $map['authService'] = fn (Container $c) =>
            new AuthService(
                $c->auth,
                $c->settingsProvider,
                $c->authTokenRepository,
                $c->userRepository
            );

        // external

        $map['gravatar'] = fn (Container $c) =>
            new Gravatar();
        
        $map['twitch'] = fn (Container $c) =>
            new Twitch(
                $this->settings['twitch']
            );

        $map['telegram'] = fn (Container $c) =>
            new Telegram(
                $this->settings['telegram']
            );

        $map['twitter'] = fn (Container $c) =>
            new Twitter(
                $this->settings['twitter']
            );

        // handlers

        $map['notFoundHandler'] = fn (Container $c) =>
            new NotFoundHandler(
                $c->appContext
            );

        $map['errorHandler'] = fn (Container $c) =>
            new ErrorHandler(
                $c->appContext
            );

        $map['notAllowedHandler'] = fn (Container $c) =>
            new NotAllowedHandler(
                $c->appContext
            );

        return $map;
    }
}
