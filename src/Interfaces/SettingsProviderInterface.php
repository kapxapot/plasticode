<?php

namespace Plasticode\Interfaces;

interface SettingsProviderInterface
{
    /**
     * Returns settings value.
     *
     * @param string $path Path to settings value
     * @param mixed $default Default value
     * @return mixed
     */
    public function getSettings(string $path = null, $default = null);
}
