<?php

namespace Plasticode;

use Plasticode\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Arrays;
use Psr\Container\ContainerInterface;

class SettingsProvider implements SettingsProviderInterface
{
    /**
     * DI container
     *
     * @var ContainerInterface
     */
    public $container;

    /**
     * @param ContainerInterface $container DI container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns settings value.
     *
     * @param string $path Path to settings value
     * @param mixed $default Default value
     * @return mixed
     */
    public function getSettings(string $path = null, $default = null)
    {
        $result = $this->container->get('settings');

        if ($path) {
            $result = Arrays::get($result, $path);
        }
        
        return $result ?? $default;
    }
}
