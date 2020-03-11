<?php

namespace Plasticode\Core\Interfaces;

interface SettingsProviderInterface
{
    /**
     * Returns settings value.
     *
     * @param string $path Path to settings value
     * @param mixed $default Default value
     * @return mixed
     */
    function get(string $path, $default = null);
}
