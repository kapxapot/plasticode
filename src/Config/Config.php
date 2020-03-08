<?php

namespace Plasticode\Config;

use Plasticode\Interfaces\SettingsProviderInterface;

abstract class Config
{
    /** @var SettingsProviderInterface */
    private $settingsProvider;

    public function __construct(SettingsProviderInterface $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * Get settings value.
     *
     * @param string $var
     * @param mixed $def
     * @return mixed
     */
    protected function get(string $var, $def = null)
    {
        return $this->settingsProvider->getSettings($var, $def);
    }
}
