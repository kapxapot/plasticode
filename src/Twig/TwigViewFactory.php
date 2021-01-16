<?php

namespace Plasticode\Twig;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Config\Config;
use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\IO\File;
use Plasticode\IO\Image;
use Plasticode\Services\AuthService;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Twig\Extensions\AccessRightsExtension;
use Plasticode\Twig\Extensions\TranslatorExtension;
use Plasticode\Twig\TwigView;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouterInterface;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Twig\Extension\DebugExtension;

class TwigViewFactory
{
    public function __invoke(ContainerInterface $container): ViewInterface
    {
        /** @var SettingsProviderInterface */
        $settingsProvider = $container->get(SettingsProviderInterface::class);

        /** @var Config */
        $config = $container->get(Config::class);

        /** @var AuthInterface */
        $auth = $container->get(AuthInterface::class);

        /** @var AuthService */
        $authService = $container->get(AuthService::class);

        /** @var LinkerInterface */
        $linker = $container->get(LinkerInterface::class);

        $tws = $settingsProvider->get('view');

        $path = $tws['templates_path'];
        $paths = is_array($path) ? $path : [$path];

        $templatesPath = array_map(
            fn ($p) => File::combine($config->rootDir(), $p),
            $paths
        );

        $cachePath = $tws['cache_path'];

        if ($cachePath) {
            $cachePath = File::combine($config->rootDir(), $cachePath);
        }

        $view = new Twig(
            $templatesPath,
            [
                'cache' => $cachePath,
                'debug' => $settingsProvider->get('debug'),
            ]
        );

        $view->addExtension(
            new TwigExtension(
                $container->get(RouterInterface::class),
                $container->get('request')->getUri()
            )
        );

        $view->addExtension(
            new AccessRightsExtension(
                $container->get(Access::class),
                $auth
            )
        );

        $view->addExtension(
            new TranslatorExtension(
                $container->get(TranslatorInterface::class)
            )
        );

        $view->addExtension(new DebugExtension());

        // set globals
        foreach ($config->viewGlobals() as $key => $value) {
            $view[$key] = $value;
        }

        $user = $auth->getUser();

        $view['auth'] = [
            'check' => $authService->check(),
            'user' => $user,
            'role' => $auth->getRole(),
            'avatar' => is_null($user)
                ? $linker->defaultGravatarUrl()
                : $user->gravatarUrl(),
        ];

        $view['image_types'] = Image::buildTypesString();

        $view['tables'] = $config->tableMetadata()->all();
        $view['entities'] = $config->entitySettings()->all();
        $view['root'] = $settingsProvider->get('root');
        $view['api'] = $settingsProvider->get('api');

        if ($settingsProvider->get('auth_token_key') !== null) {
            $view['auth_token_key'] = $settingsProvider->get('auth_token_key');
        }

        /** @var Twig $view */
        return new TwigView($view);
    }
}
