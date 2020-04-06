<?php

namespace Plasticode\Config;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
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
use Plasticode\Repositories\Idiorm\NewsRepository;
use Plasticode\Repositories\Idiorm\PageRepository;
use Plasticode\Repositories\Idiorm\RoleRepository;
use Plasticode\Repositories\Idiorm\TagRepository;
use Plasticode\Repositories\Idiorm\UserRepository;
use Plasticode\Services\AuthService;
use Plasticode\Twig\Extensions\AccessRightsExtension;
use Plasticode\Twig\TwigView;
use Plasticode\Util\Cases;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;
use Psr\Container\ContainerInterface as CI;
use Slim\Collection as SlimCollection;
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

    /**
     * Get mappings for DI container.
     */
    public function getMappings() : array
    {
        $map = [];

        $map['cache'] = fn (CI $c) =>
            new Cache();

        $map['access'] = fn (CI $c) =>
            new Access(
                $c->cache,
                $this->settings['access']
            );

        $map['settingsProvider'] = fn (CI $c) =>
            new SettingsProvider(
                $c->get('settings')
            );

        $map['db'] = function (CI $c) {
            $dbs = $this->dbSettings;
            
            \ORM::configure(
                'mysql:host=' . $dbs['host'] . ';dbname=' . $dbs['database']
            );
            
            \ORM::configure('username', $dbs['user']);
            \ORM::configure('password', $dbs['password'] ?? '');
            
            \ORM::configure(
                'driver_options',
                [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
            );
            
            return new Db(
                $c->cache,
                $c->settingsProvider
            );
        };

        $map['auth'] = fn (CI $c) =>
            new Auth(
                $c->session
            );

        $map['session'] = function (CI $c) {
            $root = $this->settings['root'];
            $name = 'sessionContainer' . $root;
            
            return new Session($name);
        };

        $map['logger'] = function (CI $c) {
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
                $path,
                Logger::DEBUG
            );

            $formatter = new LineFormatter(
                null, null, false, true
            );

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
        
            return $logger;
        };

        $map['captchaConfig'] = fn (CI $c) =>
            new CaptchaConfig();

        $map['captcha'] = fn (CI $c) =>
            new Captcha(
                $c->session,
                $c->captchaConfig
            );

        $map['generatorResolver'] = fn (CI $c) =>
            new GeneratorResolver(
                $c,
                ['\\App\\Generators']
            );

        $map['cases'] = fn (CI $c) =>
            new Cases();

        $map['repositoryContext'] = fn (CI $c) =>
            new RepositoryContext(
                $c->access,
                $c->auth,
                $c->cache,
                $c->db
            );

        $map['authTokenRepository'] = fn (CI $c) =>
            new AuthTokenRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new AuthTokenHydrator(
                        $c->userRepository
                    )
                )
            );

        $map['menuRepository'] = fn (CI $c) =>
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

        $map['menuItemRepository'] = fn (CI $c) =>
            new MenuItemRepository(
                $c->repositoryContext,
                new MenuItemHydrator(
                    $c->linker
                )
            );

        $map['newsRepository'] = fn (CI $c) =>
            new NewsRepository(
                $c->repositoryContext
            );

        $map['pageRepository'] = fn (CI $c) =>
            new PageRepository(
                $c->repositoryContext
            );

        $map['roleRepository'] = fn (CI $c) =>
            new RoleRepository(
                $c->repositoryContext
            );

        $map['tagRepository'] = fn (CI $c) =>
            new TagRepository(
                $c->repositoryContext,
                new TagHydrator(
                    $c->linker
                )
            );

        $map['userRepository'] = fn (CI $c) =>
            new UserRepository(
                $c->repositoryContext,
                new ObjectProxy(
                    fn () =>
                    new UserHydrator(
                        $c->roleRepository,
                        $c->linker
                    )
                )
            );

        $map['appContext'] = fn (CI $c) =>
            new AppContext(
                $c->settingsProvider,
                $c->translator,
                $c->validator,
                $c->view,
                $c->logger,
                $c->menuRepository
            );

        $map['view'] = function (CI $c) {
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

            $twigExt = new TwigExtension(
                $c->router,
                $c->request->getUri()
            );

            $view->addExtension($twigExt);

            $accessExt = new AccessRightsExtension(
                $c->access,
                $c->auth
            );

            $view->addExtension($accessExt);
            $view->addExtension(new DebugExtension);

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

        $map['localizationConfig'] = fn (CI $c) =>
            new LocalizationConfig();

        $map['translator'] = function (CI $c) {
            $lang = $this->settings['view_globals']['lang'] ?? 'ru';
            $loc = $c->localizationConfig->get($lang);
            
            return new Translator($loc);
        };

        $map['validator'] = fn (CI $c) =>
            new Validator(
                $c,
                $c->translator
            );

        $map['validationRules'] = fn (CI $c) =>
            new ValidationRules(
                $c->settingsProvider
            );

        $map['passwordValidation'] = fn (CI $c) =>
            new PasswordValidation(
                $c->validationRules
            );

        $map['userValidation'] = fn (CI $c) =>
            new UserValidation(
                $c->validationRules,
                $c->userRepository
            );

        $map['api'] = fn (CI $c) =>
            new Api(
                $c->access,
                $c->auth,
                $c->db,
                $c->logger,
                $c->userRepository
            );

        $map['renderer'] = fn (CI $c) =>
            new Renderer(
                $c->view
            );

        $map['pagination'] = fn (CI $c) =>
            new Pagination(
                $c->linker,
                $c->renderer
            );

        $map['linker'] = fn (CI $c) =>
            new Linker(
                $c->settingsProvider,
                $c->router
            );

        $map['replacesConfig'] = fn (CI $c) =>
            new ReplacesConfig();

        $map['cleanupParser'] = fn (CI $c) =>
            new CleanupParser(
                $c->replacesConfig
            );

        $map['bbParserConfig'] = fn (CI $c) =>
            new BBParserConfig(
                $c->linker
            );

        $map['bbParser'] = fn (CI $c) =>
            new BBParser(
                $c->bbParserConfig,
                $c->renderer
            );

        $map['tagLinkMapper'] = fn (CI $c) =>
            new TagLinkMapper(
                $c->renderer,
                $c->linker
            );

        $map['pageLinkMapper'] = fn (CI $c) =>
            new PageLinkMapper(
                $c->pageRepository,
                $c->tagRepository,
                $c->renderer,
                $c->linker,
                $c->tagLinkMapper
            );

        $map['newsLinkMapper'] = fn (CI $c) =>
            new NewsLinkMapper(
                $c->renderer,
                $c->linker
            );

        // no double brackets link mappers by default
        // add them!
        $map['doubleBracketsConfig'] = fn (CI $c) =>
            new LinkMapperSource();

        $map['doubleBracketsParser'] = fn (CI $c) =>
            new DoubleBracketsParser(
                $c->doubleBracketsConfig
            );

        $map['lineParser'] = fn (CI $c) =>
            new CompositeParser(
                $c->bbParser,
                $c->doubleBracketsParser
            );

        $map['bbContainerConfig'] = fn (CI $c) =>
            new BBContainerConfig();

        $map['bbContainerParser'] = fn (CI $c) =>
            new BBContainerParser(
                $c->bbContainerConfig,
                new BBSequencer(),
                new BBTreeBuilder(),
                new BBTreeRenderer($c->renderer)
            );

        $map['parser'] = fn (CI $c) =>
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

        $map['cutParser'] = fn (CI $c) =>
            new CutParser($c->cleanupParser);

        $map['dispatcher'] = fn (CI $c) =>
            new EventDispatcher(
                $c->eventLog,
                $c->eventProcessors
            );

        $map['eventProcessors'] = fn (CI $c) =>
            [];

        $map['eventLog'] = function (CI $c) {
            $logger = new Logger(
                $this->settings['event_log']['name']
            );
            
            $path = $this->settings['event_log']['path'];
            $path = File::absolutePath($this->dir, $path);

            $handler = new StreamHandler(
                $path,
                Logger::DEBUG
            );

            $formatter = new LineFormatter(
                null, null, false, true
            );

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
        
            return $logger;
        };

        // services

        $map['authService'] = fn (CI $c) =>
            new AuthService(
                $c->auth,
                $c->settingsProvider,
                $c->authTokenRepository,
                $c->userRepository
            );

        // external

        $map['twitch'] = fn (CI $c) =>
            new Twitch(
                $this->settings['twitch']
            );

        $map['telegram'] = fn (CI $c) =>
            new Telegram(
                $this->settings['telegram']
            );

        $map['twitter'] = fn (CI $c) =>
            new Twitter(
                $this->settings['twitter']
            );

        // handlers

        $map['notFoundHandler'] = fn (CI $c) =>
            new NotFoundHandler(
                $c->appContext
            );

        $map['errorHandler'] = fn (CI $c) =>
            new ErrorHandler(
                $c->appContext
            );

        $map['notAllowedHandler'] = fn (CI $c) =>
            new NotAllowedHandler(
                $c->appContext
            );

        return $map;
    }
}
