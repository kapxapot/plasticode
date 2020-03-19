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
use Plasticode\IO\File;
use Plasticode\IO\Image;
use Plasticode\Models\Validation\PasswordValidation;
use Plasticode\Models\Validation\UserValidation;
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
use Plasticode\Repositories\Idiorm\MenuItemRepository;
use Plasticode\Repositories\Idiorm\MenuRepository;
use Plasticode\Repositories\Idiorm\NewsRepository;
use Plasticode\Repositories\Idiorm\PageRepository;
use Plasticode\Repositories\Idiorm\RoleRepository;
use Plasticode\Repositories\Idiorm\TagRepository;
use Plasticode\Repositories\Idiorm\UserRepository;
use Plasticode\Twig\Extensions\AccessRightsExtension;
use Plasticode\Twig\TwigView;
use Plasticode\Util\Cases;
use Plasticode\Validation\ValidationRules;
use Plasticode\Validation\Validator;
use Psr\Container\ContainerInterface;
use Slim\Collection as SlimCollection;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig\Extension\DebugExtension;

class Bootstrap
{
    /** @var SlimCollection */
    protected $settings;

    /**
     * Database settings
     *
     * @var array
     */
    protected $dbSettings;

    /**
     * Current directory
     *
     * @var string
     */
    protected $dir;
    
    public function __construct(SlimCollection $settings, string $dir)
    {
        $this->settings = $settings;
        $this->dbSettings = $this->settings['db'];
        $this->dir = $dir;
    }
    
    /**
     * Get mappings for DI container.
     *
     * @return array
     */
    public function getMappings() : array
    {
        return [
            'db' => function (ContainerInterface $container) {
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
                    $container->access,
                    $container->cache,
                    $container->settingsProvider,
                    $container->userRepository
                );
            },

            'authTokenRepository' => function (ContainerInterface $container) {
                return new AuthTokenRepository(
                    $container->db,
                    $container->userRepository
                );
            },
            
            'menuRepository' => function (ContainerInterface $container) {
                return new MenuRepository(
                    $container->db,
                    $container->menuItemRepository,
                    $container->linker
                );
            },
            
            'menuItemRepository' => function (ContainerInterface $container) {
                return new MenuItemRepository(
                    $container->db,
                    $container->linker
                );
            },

            'newsRepository' => function (ContainerInterface $container) {
                return new NewsRepository(
                    $container->db
                );
            },

            'pageRepository' => function (ContainerInterface $container) {
                return new PageRepository(
                    $container->db
                );
            },

            'roleRepository' => function (ContainerInterface $container) {
                return new RoleRepository(
                    $container->db
                );
            },

            'tagRepository' => function (ContainerInterface $container) {
                return new TagRepository(
                    $container->db,
                    $container->linker
                );
            },

            'userRepository' => function (ContainerInterface $container) {
                return new UserRepository(
                    $container->db
                );
            },

            'appContext' => function (ContainerInterface $container) {
                return new AppContext(
                    $container->settingsProvider,
                    $container->translator,
                    $container->validator,
                    $container->view,
                    $container->logger,
                    $container->notFoundHandler,
                    $container->menuRepository
                );
            },

            'settingsProvider' => function (ContainerInterface $container) {
                return new SettingsProvider(
                    $container->get('settings')
                );
            },
            
            'session' => function (ContainerInterface $container) {
                $root = $this->settings['root'];
                $name = 'sessionContainer' . $root;
                
                return new Session($name);
            },
            
            'cache' => function (ContainerInterface $container) {
                return new Cache();
            },
            
            'auth' => function (ContainerInterface $container) {
                return new Auth(
                    $container->session,
                    $container->settingsProvider,
                    $container->authTokenRepository,
                    $container->userRepository
                );
            },
            
            'logger' => function (ContainerInterface $container) {
                $logger = new Logger(
                    $this->settings['logger']['name']
                );
            
                $logger->pushProcessor(
                    function ($record) use ($container) {
                        $user = $container->auth->getUser();

                        if ($user) {
                            $record['extra']['user'] = $user->toString();
                        }
                        
                        $token = $container->auth->getToken();

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
            },
            
            'captchaConfig' => function (ContainerInterface $container) {
                return new CaptchaConfig();
            },
            
            'captcha' => function (ContainerInterface $container) {
                return new Captcha(
                    $container->session,
                    $container->captchaConfig
                );
            },
            
            'access' => function (ContainerInterface $container) {
                return new Access(
                    $container->auth,
                    $container->cache,
                    $this->settings['access']
                );
            },
            
            'generatorResolver' => function (ContainerInterface $container) {
                return new GeneratorResolver(
                    $container,
                    ['\\App\\Generators']
                );
            },
            
            'cases' => function (ContainerInterface $container) {
                return new Cases();
            },
            
            'view' => function (ContainerInterface $container) {
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
                    $container->router,
                    $container->request->getUri()
                );
                
                $view->addExtension($twigExt);

                $accessExt = new AccessRightsExtension(
                    $container->access
                );

                $view->addExtension($accessExt);
                $view->addExtension(new DebugExtension);

                // set globals
                $globals = $this->settings['view_globals'];
                foreach ($globals as $key => $value) {
                    $view[$key] = $value;
                }

                $user = $container->auth->getUser();
            
                $view['auth'] = [
                    'check' => $container->auth->check(),
                    'user' => $user,
                    'role' => $container->auth->getRole(),
                    'avatar' => is_null($user)
                        ? $container->linker->defaultGravatarUrl()
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
            },
            
            'localizationConfig' => function (ContainerInterface $container) {
                return new LocalizationConfig();
            },
            
            'translator' => function (ContainerInterface $container) {
                $lang = $this->settings['view_globals']['lang'] ?? 'ru';
                $loc = $container->localizationConfig->get($lang);
                
                return new Translator($loc);
            },
            
            'validator' => function (ContainerInterface $container) {
                return new Validator(
                    $container,
                    $container->translator
                );
            },

            'validationRules' => function (ContainerInterface $container) {
                return new ValidationRules(
                    $container->settingsProvider
                );
            },

            'passwordValidation' => function (ContainerInterface $container) {
                return new PasswordValidation(
                    $container->validationRules
                );
            },

            'userValidation' => function (ContainerInterface $container) {
                return new UserValidation(
                    $container->validationRules,
                    $container->userRepository
                );
            },
            
            'api' => function (ContainerInterface $container) {
                return new Api(
                    $container->db,
                    $container->auth,
                    $container->logger
                );
            },
            
            'renderer' => function (ContainerInterface $container) {
                return new Renderer($container->view);
            },
            
            'pagination' => function (ContainerInterface $container) {
                return new Pagination(
                    $container->linker,
                    $container->renderer
                );
            },
            
            'linker' => function (ContainerInterface $container) {
                return new Linker(
                    $container->settingsProvider,
                    $container->router
                );
            },
            
            'replacesConfig' => function (ContainerInterface $container) {
                return new ReplacesConfig();
            },

            'cleanupParser' => function (ContainerInterface $container) {
                return new CleanupParser($container->replacesConfig);
            },

            'bbParserConfig' => function (ContainerInterface $container) {
                return new BBParserConfig($container->linker);
            },

            'bbParser' => function (ContainerInterface $container) {
                return new BBParser(
                    $container->bbParserConfig,
                    $container->renderer
                );
            },

            'tagLinkMapper' => function (ContainerInterface $container) {
                return new TagLinkMapper(
                    $container->renderer,
                    $container->linker
                );
            },

            'pageLinkMapper' => function (ContainerInterface $container) {
                return new PageLinkMapper(
                    $container->pageRepository,
                    $container->tagRepository,
                    $container->renderer,
                    $container->linker,
                    $container->tagLinkMapper
                );
            },

            'newsLinkMapper' => function (ContainerInterface $container) {
                return new NewsLinkMapper(
                    $container->renderer,
                    $container->linker
                );
            },

            'doubleBracketsConfig' => function (ContainerInterface $container) {
                // no double brackets link mappers by default
                // add them!
                return new LinkMapperSource();
            },

            'doubleBracketsParser' => function (ContainerInterface $container) {
                return new DoubleBracketsParser(
                    $container->doubleBracketsConfig
                );
            },

            'lineParser' => function (ContainerInterface $container) {
                return new CompositeParser(
                    [
                        $container->bbParser,
                        $container->doubleBracketsParser,
                    ]
                );
            },

            'bbContainerConfig' => function (ContainerInterface $container) {
                return new BBContainerConfig();
            },

            'bbContainerParser' => function (ContainerInterface $container) {
                return new BBContainerParser(
                    $container->bbContainerConfig,
                    new BBSequencer(),
                    new BBTreeBuilder(),
                    new BBTreeRenderer($container->renderer)
                );
            },
            
            'parser' => function (ContainerInterface $container) {
                return new CompositeParser(
                    [
                        new TitlesStep($container->renderer, $container->lineParser),
                        new MarkdownParser($container->renderer),
                        new NewLinesToBrsStep(),
                        $container->bbContainerParser,
                        $container->bbParser,
                        new ReplacesStep($container->replacesConfig),
                        $container->doubleBracketsParser,
                        $container->cleanupParser
                    ]
                );
            },

            'cutParser' => function (ContainerInterface $container) {
                return new CutParser($container->cleanupParser);
            },

            'dispatcher' => function (ContainerInterface $container) {
                return new EventDispatcher(
                    $container->eventLog,
                    $container->eventProcessors
                );
            },

            'eventProcessors' => function (ContainerInterface $container) {
                return [];
            },
                
            'eventLog' => function (ContainerInterface $container) {
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
            },
        
            // external
            
            'twitch' => function (ContainerInterface $container) {
                return new Twitch(
                    $this->settings['twitch']
                );
            },
            
            'telegram' => function (ContainerInterface $container) {
                return new Telegram(
                    $this->settings['telegram']
                );
            },
            
            'twitter' => function (ContainerInterface $container) {
                return new Twitter(
                    $this->settings['twitter']
                );
            },
            
            // handlers
            
            'notFoundHandler' => function (ContainerInterface $container) {
                return new NotFoundHandler(
                    $container,
                    $container->translator,
                    $container->view
                );
            },
            
            'errorHandler' => function (ContainerInterface $container) {
                return new ErrorHandler(
                    $container
                );
            },
            
            'notAllowedHandler' => function (ContainerInterface $container) {
                return new NotAllowedHandler(
                    $container
                );
            },
        ];
    }
}
