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
    
    protected function getClassMappings()
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
    
    protected function getContainedClassMappings()
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
    
    public function getMappings()
    {
        return [
            'userClass' => function ($container) {
                return \Plasticode\Models\User::class;
            },
            
            'roleClass' => function ($container) {
                return \Plasticode\Models\Role::class;
            },
            
            'authTokenClass' => function ($container) {
                return \Plasticode\Models\AuthToken::class;
            },
            
            'menuClass' => function ($container) {
                return \Plasticode\Models\Menu::class;
            },
            
            'menuItemClass' => function ($container) {
                return \Plasticode\Models\MenuItem::class;
            },
            
            'userRepository' => function ($container) {
                return new \Plasticode\StaticProxy($container->userClass);
            },
            
            'roleRepository' => function ($container) {
                return new \Plasticode\StaticProxy($container->roleClass);
            },
            
            'authTokenRepository' => function ($container) {
                return new \Plasticode\StaticProxy($container->authTokenClass);
            },
            
            'menuRepository' => function ($container) {
                return new \Plasticode\StaticProxy($container->menuClass);
            },
            
            'menuItemRepository' => function ($container) {
                return new \Plasticode\StaticProxy($container->menuItemClass);
            },
            
            'auth' => function ($container) {
            	return new \Plasticode\Auth\Auth($container);
            },
            
            'logger' => function ($container) {
                $logger = new \Monolog\Logger($this->settings['logger']['name']);
            
                $logger->pushProcessor(function ($record) use ($container) {
                	$user = $container->auth->getUser();
                	if ($user) {
            	    	$record['extra']['user'] = $container->auth->userString();
                	}
            	    
            	    $token = $container->auth->getToken();
            	    if ($token) {
            	    	$record['extra']['token'] = $container->auth->tokenString();
            	    }
            	
            	    return $record;
            	});
            
                $logger->pushHandler(new \Monolog\Handler\StreamHandler($this->dir . $this->settings['logger']['path'], \Monolog\Logger::DEBUG));
            
                return $logger;
            },
            
            'captchaConfig' => function ($container) {
                return new \Plasticode\Config\Captcha;  
            },
            
            'captcha' => function ($container) {
            	return new \Plasticode\Auth\Captcha($container, $container->captchaConfig->getReplaces());
            },
            
            'access' => function ($container) {
            	return new \Plasticode\Auth\Access($container);
            },
            
            'generatorResolver' => function ($container) {
            	return new \Plasticode\Generators\GeneratorResolver($container, [ '\\App\\Generators' ]);
            },
            
            'cases' => function ($container) {
            	return new \Plasticode\Util\Cases;
            },
            
            'view' => function ($container) {
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
            
            	$view->addExtension(new \Slim\Views\TwigExtension($container->router, $container->request->getUri()));
            	$view->addExtension(new \Plasticode\Twig\Extensions\AccessRightsExtension($container));
            	$view->addExtension(new \Twig\Extension\DebugExtension);

            	// set globals
                $globals = $this->settings['view_globals'];
            	foreach ($globals as $key => $value) {
            		$view[$key] = $value;
            	}
            
            	$view['auth'] = [
            		'check' => $container->auth->check(),
            		'user' => $container->auth->getUser(),
            		'role' => $container->auth->getRole(),
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
            
            'cache' => function ($container) {
            	return new \Plasticode\Core\Cache;
            },
            
            'session' => function ($container) {
                $root = $this->settings['root'];
            	$name = 'sessionContainer' . $root;
            	
            	return new \Plasticode\Core\Session($name);
            },
            
            'localization' => function ($container) {
                return new \Plasticode\Config\Localization;
            },
            
            'translator' => function ($container) {
                $lang = $this->settings['view_globals']['lang'];
                $loc = $container->localization->get($lang);
                
            	return new \Plasticode\Core\Translator($loc);
            },
            
            'validator' => function ($container) {
            	return new \Plasticode\Validation\Validator($container);
            },
            
            'dbClass' => function ($container) {
                return \Plasticode\Data\Db::class;
            },
            
            'db' => function ($container) {
                $dbs = $this->dbSettings;
                
            	\ORM::configure("mysql:host={$dbs['host']};dbname={$dbs['database']}");
            	\ORM::configure("username", $dbs['user']);
            	\ORM::configure("password", $dbs['password']);
            	\ORM::configure("driver_options", array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
            	
            	$dbClass = $container->dbClass;
            	
            	return new $dbClass($container);
            },
            
            'renderer' => function ($container) {
            	return new \Plasticode\Core\Renderer($container->view);
            },
            
            'pagination' => function ($container) {
            	return new \Plasticode\Core\Pagination($container->linker, $container->renderer);
            },
            
            'linker' => function ($container) {
            	return new \Plasticode\Core\Linker($container);
            },
            
            'parserConfig' => function ($container) {
            	return new \Plasticode\Config\Parsing;
            },
            
            'parser' => function ($container) {
            	return new \Plasticode\Core\Parser($container, $container->parserConfig);
            },
            
            // external
            
            'twitch' => function ($container) {
            	return new \Plasticode\External\Twitch($this->settings['twitch']);
            },
            
            'telegram' => function ($container) {
            	return new \Plasticode\External\Telegram($this->settings['telegram']);
            },
            
            'twitter' => function ($container) {
            	return new \Plasticode\External\Twitter($this->settings['twitter']);
            },
            
            // handlers
            
            'notFoundHandler' => function ($container) {
            	return new \Plasticode\Handlers\NotFoundHandler($container);
            },
            
            'errorHandler' => function ($container) {
            	return new \Plasticode\Handlers\ErrorHandler($container, $this->debug);
            },
            
            'notAllowedHandler' => function ($container) {
            	return new \Plasticode\Handlers\NotAllowedHandler($container);
            },
        ];
    }
}
