<?php

namespace Plasticode\Data;

use Plasticode\Core\Interfaces\SettingsProviderInterface;

/**
 * Provides ORM-agnostic database metadata settings.
 */
class DbMetadata
{
    private SettingsProviderInterface $settingsProvider;

    /**
     * Tables settings.
     */
    private ?array $tables = null;

    public function __construct(
        SettingsProviderInterface $settingsProvider
    )
    {
        $this->settingsProvider = $settingsProvider;
    }

    public function tableSettings(string $tableAlias) : ?array
    {
        $this->tables ??= $this->settingsProvider->get('tables');

        return $this->tables[$tableAlias] ?? null;
    }

    public function tableName(string $tableAlias) : string
    {
        $tableSettings = $this->tableSettings($tableAlias);

        return $tableSettings['table'] ?? $tableAlias;
    }

    public function fields(string $tableAlias) : ?array
    {
        $tableSettings = $this->tableSettings($tableAlias);

        return $tableSettings['fields'] ?? null;
    }

    public function hasField(string $tableAlias, string $field) : bool
    {
        $tableSettings = $this->tableSettings($tableAlias);
        $has = $tableSettings['has'] ?? null;

        return is_array($has) && in_array($field, $has);
    }
}
