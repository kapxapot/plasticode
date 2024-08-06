<?php

namespace Plasticode\Settings\Interfaces;

use ArrayAccess;

interface SettingsProviderInterface
{
    /**
     * Returns settings value.
     *
     * @param string $path Path to settings value
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $path, $default = null);

    /**
     * @return array|ArrayAccess
     */
    public function all();
}
