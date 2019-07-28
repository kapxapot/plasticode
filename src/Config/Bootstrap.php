<?php

namespace Plasticode\Config;

use Plasticode\IO\File;
use Psr\Container\ContainerInterface;
use Slim\Collection;

class Bootstrap
{
    /**
     * Settings
     *
     * @var Slim\Collection
     */
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
    
    public function __construct(Collection $settings, string $dir)
    {
        $this->settings = $settings;
        $this->dbSettings = $this->settings['db'];
        $this->dir = $dir;
    }
    
    /**
     * Not used yet
     *
     * @return array
     */
    protected function getClassMappings() : array
    {
        return [
            'user' => \Plasticode\Models\User::class,
            'role' => \Plasticode\Models\Role::class,
            'authToken' => \Plasticode\Models\AuthToken::class,
            'menuItem' => \Plasticode\Models\MenuItem::class,

            'captchaConfig' => \Plasticode\Config\Captcha::class,
            'parserConfig' => \Plasticode\Config\Parsing::class,

            'db' => \Plasticode\Data\Db::class,
            'cache' => \Plasticode\Core\Cache::class,
            'cases' => \Plasticode\Util\Cases::class,
            'localization' => \Plasticode\Config\Localization::class,
        ];
    }
    
    /**
     * Not used yet
     *
     * @return array
     */
    protected function getContainedClassMappings() : array
    {
        return [
            'auth' => \Plasticode\Auth\Auth::class,
            'access' => \Plasticode\Auth\Access::class,
            'validator' => \Plasticode\Validation\Validator::class,
            'decorator' => \Plasticode\Core\Decorator::class,
            'builder' => \Plasticode\Core\Builder::class,
            'linker' => \Plasticode\Core\Linker::class,

            'twitch' => \Plasticode\External\Twitch::class,
            'telegram' => \Plasticode\External\Telegram::class,

            'notFoundHandler' => \Plasticode\Handlers\NotFoundHandler::class,
            'notAllowedHandler' => \Plasticode\Handlers\NotAllowedHandler::class,
        ];
    }
    
    /**
     * Get mappings for DI container
     *
     * @return array
     */
    public function getMappings() : array
    {
        return [
            'userClass' => function (ContainerInterface $container) {
                return \Plasticode\Models\User::class;
            },
            
            'roleClass' => function (ContainerInterface $container) {
                return \Plasticode\Models\Role::class;
            },
            
            'authTokenClass' => function (ContainerInterface $container) {
                return \Plasticode\Models\AuthToken::class;
            },
            
            'menuClass' => function (ContainerInterface $container) {
                return \Plasticode\Models\Menu::class;
            },
            
            'menuItemClass' => function (ContainerInterface $container) {
                return \Plasticode\Models\MenuItem::class;
            },
            
            'userRepository' => function (ContainerInterface $container) {
                return new \Plasticode\StaticProxy($container->userClass);
            },
            
            'roleRepository' => function (ContainerInterface $container) {
                return new \Plasticode\StaticProxy($container->roleClass);
            },
            
            'authTokenRepository' => function (ContainerInterface $container) {
                return new \Plasticode\StaticProxy($container->authTokenClass);
            },
            
            'menuRepository' => function (ContainerInterface $container) {
                return new \Plasticode\StaticProxy($container->menuClass);
            },
            
            'menuItemRepository' => function (ContainerInterface $container) {
                return new \Plasticode\StaticProxy($container->menuItemClass);
            },
            
            'auth' => function (ContainerInterface $container) {
                return new \Plasticode\Auth\Auth($container);
            },
            
            'logger' => function (ContainerInterface $container) {
                $logger = new \Monolog\Logger($this->settings['logger']['name']);
            
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

                $handler = new \Monolog\Handler\StreamHandler(
                    $path,
                    \Monolog\Logger::DEBUG
                );

                $formatter = new \Monolog\Formatter\LineFormatter(
                    null, null, false, true
                );

                $handler->setFormatter($formatter);
            
                $logger->pushHandler($handler);
            
                return $logger;
            },
            
            'captchaConfig' => function (ContainerInterface $container) {
                return new \Plasticode\Config\Captcha();
            },
            
            'captcha' => function (ContainerInterface $container) {
                return new \Plasticode\Auth\Captcha(
                    $container,
                    $container->captchaConfig->getReplaces()
                );
            },
            
            'access' => function (ContainerInterface $container) {
                $auth = $container->auth;
                $accessSettings = $this->settings['access'];

                return new \Plasticode\Auth\Access($auth, $accessSettings);
            },
            
            'generatorResolver' => function (ContainerInterface $container) {
                return new \Plasticode\Generators\GeneratorResolver(
                    $container,
                    ['\\App\\Generators']
                );
            },
            
            'cases' => function (ContainerInterface $container) {
                return new \Plasticode\Util\Cases();
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
            
                $view = new \Slim\Views\Twig(
                    $templatesPath,
                    [
                        'cache' => $cachePath,
                        'debug' => $this->settings['debug'],
                    ]
                );
            
                $twigExt = new \Slim\Views\TwigExtension(
                    $container->router,
                    $container->request->getUri()
                );
                
                $view->addExtension($twigExt);

                $accessExt = new \Plasticode\Twig\Extensions\AccessRightsExtension(
                    $container->access
                );

                $view->addExtension($accessExt);

                $view->addExtension(new \Twig\Extension\DebugExtension);

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
            
                $view['image_types'] = \Plasticode\IO\Image::buildTypesString();
                
                $view['tables'] = $this->settings['tables'];
                $view['entities'] = $this->settings['entities'];
                
                $view['root'] = $this->settings['root'];
                $view['api'] = $this->settings['api'];
            
                if (isset($this->settings['auth_token_key'])) {
                    $view['auth_token_key'] = $this->settings['auth_token_key'];
                }
            
                return $view;
            },
            
            'cache' => function (ContainerInterface $container) {
                return new \Plasticode\Core\Cache();
            },
            
            'session' => function (ContainerInterface $container) {
                $root = $this->settings['root'];
                $name = 'sessionContainer' . $root;
                
                return new \Plasticode\Core\Session($name);
            },
            
            'localization' => function (ContainerInterface $container) {
                return new \Plasticode\Config\Localization();
            },
            
            'translator' => function (ContainerInterface $container) {
                $lang = $this->settings['view_globals']['lang'] ?? 'ru';
                $loc = $container->localization->get($lang);
                
                return new \Plasticode\Core\Translator($loc);
            },
            
            'validator' => function (ContainerInterface $container) {
                return new \Plasticode\Validation\Validator($container);
            },
            
            'dbClass' => function (ContainerInterface $container) {
                return \Plasticode\Data\Db::class;
            },
            
            'db' => function (ContainerInterface $container) {
                $dbs = $this->dbSettings;
                
                \ORM::configure(
                    'mysql:host=' . $dbs['host'] .
                    ';dbname=' . $dbs['database']
                );
                
                \ORM::configure('username', $dbs['user']);
                \ORM::configure('password', $dbs['password'] ?? '');
                
                \ORM::configure(
                    'driver_options',
                    [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']
                );
                
                $dbClass = $container->dbClass;
                
                return new $dbClass($container);
            },

            'api' => function (ContainerInterface $container) {
                return new \Plasticode\Data\Api($container);
            },
            
            'renderer' => function (ContainerInterface $container) {
                return new \Plasticode\Core\Renderer($container->view);
            },
            
            'pagination' => function (ContainerInterface $container) {
                return new \Plasticode\Core\Pagination(
                    $container->linker,
                    $container->renderer
                );
            },
            
            'linker' => function (ContainerInterface $container) {
                return new \Plasticode\Core\Linker($container);
            },
            
            'parserConfig' => function (ContainerInterface $container) {
                return new \Plasticode\Config\Parsing();
            },
            
            'parser' => function (ContainerInterface $container) {
                return new \Plasticode\Core\Parser(
                    $container,
                    $container->parserConfig
                );
            },

            'dispatcher' => function (ContainerInterface $container) {
                return new \Plasticode\Events\EventDispatcher(
                    $container,
                    $container->eventProcessors
                );
            },

            'eventProcessors' => function (ContainerInterface $container) {
                return [];
            },
                
            'eventLog' => function (ContainerInterface $container) {
                $logger = new \Monolog\Logger($this->settings['event_log']['name']);
                
                $path = $this->settings['event_log']['path'];
                
                $path = File::absolutePath($this->dir, $path);

                $handler = new \Monolog\Handler\StreamHandler(
                    $path,
                    \Monolog\Logger::DEBUG
                );

                $formatter = new \Monolog\Formatter\LineFormatter(
                    null, null, false, true
                );

                $handler->setFormatter($formatter);
            
                $logger->pushHandler($handler);
            
                return $logger;
            },
        
            // external
            
            'twitch' => function (ContainerInterface $container) {
                return new \Plasticode\External\Twitch(
                    $this->settings['twitch']
                );
            },
            
            'telegram' => function (ContainerInterface $container) {
                return new \Plasticode\External\Telegram(
                    $this->settings['telegram']
                );
            },
            
            'twitter' => function (ContainerInterface $container) {
                return new \Plasticode\External\Twitter(
                    $this->settings['twitter']
                );
            },
            
            // handlers
            
            'notFoundHandler' => function (ContainerInterface $container) {
                return new \Plasticode\Handlers\NotFoundHandler($container);
            },
            
            'errorHandler' => function (ContainerInterface $container) {
                return new \Plasticode\Handlers\ErrorHandler($container);
            },
            
            'notAllowedHandler' => function (ContainerInterface $container) {
                return new \Plasticode\Handlers\NotAllowedHandler($container);
            },
        ];
    }
}
