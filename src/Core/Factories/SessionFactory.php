<?php

namespace Plasticode\Core\Factories;

use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Session;
use Plasticode\DI\Interfaces\ContainerFactoryInterface;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;

class SessionFactory implements ContainerFactoryInterface
{
    public function __invoke(ContainerInterface $container): SessionInterface
    {
        /** @var SettingsProviderInterface */
        $settingsProvider = $container->get(SettingsProviderInterface::class);

        $root = $settingsProvider->get('root');
        $name = 'sessionContainer' . $root;

        return new Session($name);
    }
}
