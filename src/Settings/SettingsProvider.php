<?php

namespace Plasticode\Settings;

use ArrayAccess;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Util\Arrays;

class SettingsProvider implements SettingsProviderInterface
{
    /** @var array|ArrayAccess */
    public $settings;

    /**
     * @param array|ArrayAccess|null $settings
     */
    public function __construct($settings = null)
    {
        $this->settings = $settings ?? [];
    }

    public function get(string $path, $default = null)
    {
        return Arrays::get($this->settings, $path) ?? $default;
    }

    public function all()
    {
        return $this->settings;
    }
}
