<?php

namespace Plasticode\Core\Factories;

use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Session;
use Psr\Container\ContainerInterface;

class SessionFactory
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
