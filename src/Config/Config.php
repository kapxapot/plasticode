<?php

namespace Plasticode\Config;

use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Plasticode\Settings\SettingsProvider;

class Config
{
    const ROOT_DIR_PATH = 'root_dir';

    private SettingsProviderInterface $settingsProvider;

    public function __construct(SettingsProviderInterface $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * Root application directory path.
     */
    public function rootDir(): string
    {
        return $this->get(self::ROOT_DIR_PATH);
    }

    /**
     * Access settings.
     */
    public function accessSettings(): SettingsProviderInterface
    {
        return new SettingsProvider(
            $this->get('access', [])
        );
    }

    /**
     * Database table metadata.
     */
    public function tableMetadata(): SettingsProviderInterface
    {
        return new SettingsProvider(
            $this->get('tables', [])
        );
    }

    /**
     * Entity settings.
     */
    public function entitySettings(): SettingsProviderInterface
    {
        return new SettingsProvider(
            $this->get('entities', [])
        );
    }

    /**
     * View global settings.
     */
    public function viewGlobals(): array
    {
        return $this->get('view_globals', []);
    }

    /**
     * Get settings value.
     *
     * @param mixed $def
     * @return mixed
     */
    protected function get(string $var, $def = null)
    {
        return $this->settingsProvider->get($var, $def);
    }
}
