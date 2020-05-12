<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Arrays;

class SettingsProvider implements SettingsProviderInterface
{
    /** @var array|\ArrayAccess */
    public $settings;

    /**
     * @param array|\ArrayAccess $settings
     */
    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Returns settings value.
     *
     * @param string $path Path to settings value
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $path, $default = null)
    {
        return Arrays::get($this->settings, $path) ?? $default;
    }
}
