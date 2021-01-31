<?php

namespace Plasticode\Core\Factories;

use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Core\Session;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

class SessionFactory
{
    public function __invoke(
        SettingsProviderInterface $settingsProvider
    ): SessionInterface
    {
        $root = $settingsProvider->get('root');
        $name = 'sessionContainer' . $root;

        return new Session($name);
    }
}
