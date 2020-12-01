<?php

namespace Plasticode\Core;

use ArrayAccess;
use Plasticode\Core\Interfaces\SettingsProviderInterface;
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
}
