<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Arrays;

class SettingsProvider implements SettingsProviderInterface
{
    /**
     * Settings array
     *
     * @var array
     */
    public $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings ?? [];
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
        return Arrays::get($this->result, $path) ?? $default;
    }
}
