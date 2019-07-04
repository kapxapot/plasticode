<?php

namespace Plasticode\Config;

use Plasticode\Util\Cases;

class Bootstrap
{
    protected $settings;
    protected $dbSettings;
    protected $debug;
    protected $dir;
    
    public function __construct($settings, $debug, $dir)
    {
        $this->settings = $settings;
        $this->dbSettings = $this->settings['db'];
        $this->debug = $debug;
        $this->dir = $dir;
    }
    
    /**
     * Not used yet.
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
     * Not used yet.
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
     * Get mappings for DI container.
     *
     * @return array
     */
    public function getMappings() : array
    {
        return [
            'userClass' => function ($c) {
                return \Plasticode\Models\User::class;
            },
            
            'roleClass' => function ($c) {
                return \Plasticode\Models\Role::class;
            },
            
            'authTokenClass' => function ($c) {
                return \Plasticode\Models\AuthToken::class;
            },
            
            'menuClass' => function ($c) {
                return \Plasticode\Models\Menu::class;
            },
            
            'menuItemClass' => function ($c) {
                return \Plasticode\Models\MenuItem::class;
            },
            
            'userRepository' => function ($c) {
                return new \Plasticode\StaticProxy($c->userClass);
            },
            
            'roleRepository' => function ($c) {
                return new \Plasticode\StaticProxy($c->roleClass);
            },
            
            'authTokenRepository' => function ($c) {
                return new \Plasticode\StaticProxy($c->authTokenClass);
            },
            
            'menuRepository' => function ($c) {
                return new \Plasticode\StaticProxy($c->menuClass);
            },
            
            'menuItemRepository' => function ($c) {
                return new \Plasticode\StaticProxy($c->menuItemClass);
            },
            
            'auth' => function ($c) {
                return new \Plasticode\Auth\Auth($c);
            },
            
            'logger' => function ($c) {
                $logger = new \Monolog\Logger($this->settings['logger']['name']);
            
                $logger->pushProcessor(function ($record) use ($c) {
                    $user = $c->auth->getUser();

                    if ($user) {
                        $record['extra']['user'] = $user->toString();
                    }
                    
                    $token = $c->auth->getToken();

                    if ($token) {
                        $record['extra']['token'] = $token->toString();
                    }
                
                    return $record;
                });

                $path = $this->settings['logger']['path'];
                
                // if relative path, make it absolute
                if (\Plasticode\Util\Strings::startsWith($path, '/../')) {
                    $path = $this->dir . $path;
                }

                $handler = new \Monolog\Handler\StreamHandler(
                    $path,
                    \Monolog\Logger::DEBUG
                );
            
                $logger->pushHandler($handler);
            
                return $logger;
            },
            
            'captchaConfig' => function ($c) {
                return new \Plasticode\Config\Captcha;  
            },
            
            'captcha' => function ($c) {
                return new \Plasticode\Auth\Captcha($c, $c->captchaConfig->getReplaces());
            },
            
            'access' => function ($c) {
                return new \Plasticode\Auth\Access($c);
            },
            
            'generatorResolver' => function ($c) {
                return new \Plasticode\Generators\GeneratorResolver($c, [ '\\App\\Generators' ]);
            },
            
            'cases' => function ($c) {
                return new \Plasticode\Util\Cases;
            },
            
            'view' => function ($c) {
                $tws = $this->settings['view'];
            
                $path = $tws['templates_path'];
                $path = is_array($path) ? $path : [ $path ];
            
                $templatesPath = array_map(function ($p) {
                    return $this->dir . $p;
                }, $path);
            
                $cachePath = $tws['cache_path'];
                if ($cachePath) {
                    $cachePath = $this->dir . $cachePath;
                }
            
                $view = new \Slim\Views\Twig($templatesPath, [
                    'cache' => $cachePath,
                    'debug' => $this->debug,
                ]);
            
                $view->addExtension(new \Slim\Views\TwigExtension($c->router, $c->request->getUri()));
                $view->addExtension(new \Plasticode\Twig\Extensions\AccessRightsExtension($c));
                $view->addExtension(new \Twig\Extension\DebugExtension);

                // set globals
                $globals = $this->settings['view_globals'];
                foreach ($globals as $key => $value) {
                    $view[$key] = $value;
                }
            
                $view['auth'] = [
                    'check' => $c->auth->check(),
                    'user' => $c->auth->getUser(),
                    'role' => $c->auth->getRole(),
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
            
            'cache' => function ($c) {
                return new \Plasticode\Core\Cache;
            },
            
            'session' => function ($c) {
                $root = $this->settings['root'];
                $name = 'sessionContainer' . $root;
                
                return new \Plasticode\Core\Session($name);
            },
            
            'localization' => function ($c) {
                return new \Plasticode\Config\Localization();
            },
            
            'translator' => function ($c) {
                $lang = $this->settings['view_globals']['lang'];
                $loc = $c->localization->get($lang);
                
                return new \Plasticode\Core\Translator($loc);
            },
            
            'validator' => function ($c) {
                return new \Plasticode\Validation\Validator($c);
            },
            
            'dbClass' => function ($c) {
                return \Plasticode\Data\Db::class;
            },
            
            'db' => function ($c) {
                $dbs = $this->dbSettings;
                
                \ORM::configure("mysql:host={$dbs['host']};dbname={$dbs['database']}");
                \ORM::configure("username", $dbs['user']);
                \ORM::configure("password", $dbs['password']);
                \ORM::configure("driver_options", array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
                
                $dbClass = $c->dbClass;
                
                return new $dbClass($c);
            },
            
            'renderer' => function ($c) {
                return new \Plasticode\Core\Renderer($c->view);
            },
            
            'pagination' => function ($c) {
                return new \Plasticode\Core\Pagination($c->linker, $c->renderer);
            },
            
            'linker' => function ($c) {
                return new \Plasticode\Core\Linker($c);
            },
            
            'parserConfig' => function ($c) {
                return new \Plasticode\Config\Parsing;
            },
            
            'parser' => function ($c) {
                return new \Plasticode\Core\Parser($c, $c->parserConfig);
            },

            'dispatcher' => function ($c) {
                return new \Plasticode\Events\EventDispatcher($c->eventProcessors);
            },

            'eventProcessors' => function ($c) {
                return [];
            },
                
            'eventLog' => function ($c) {
                $logger = new \Monolog\Logger($this->settings['event_log']['name']);
                
                $path = $this->settings['event_log']['path'];
                
                // if relative path, make it absolute
                if (\Plasticode\Util\Strings::startsWith($path, '/../')) {
                    $path = $this->dir . $path;
                }

                $handler = new \Monolog\Handler\StreamHandler(
                    $path,
                    \Monolog\Logger::DEBUG
                );
            
                $logger->pushHandler($handler);
            
                return $logger;
            },
        
            // external
            
            'twitch' => function ($c) {
                return new \Plasticode\External\Twitch($this->settings['twitch']);
            },
            
            'telegram' => function ($c) {
                return new \Plasticode\External\Telegram($this->settings['telegram']);
            },
            
            'twitter' => function ($c) {
                return new \Plasticode\External\Twitter($this->settings['twitter']);
            },
            
            // handlers
            
            'notFoundHandler' => function ($c) {
                return new \Plasticode\Handlers\NotFoundHandler($c);
            },
            
            'errorHandler' => function ($c) {
                return new \Plasticode\Handlers\ErrorHandler($c, $this->debug);
            },
            
            'notAllowedHandler' => function ($c) {
                return new \Plasticode\Handlers\NotAllowedHandler($c);
            },
        ];
    }
}
