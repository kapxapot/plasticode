<?php

namespace Plasticode\Data;

use Plasticode\Config\Config;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

/**
 * Provides database tables metadata.
 */
class DbMetadata
{
    private SettingsProviderInterface $metadata;

    public function __construct(Config $config)
    {
        $this->metadata = $config->tableMetadata();
    }

    public function tableMetadata(string $tableAlias): ?array
    {
        return $this->metadata->get($tableAlias);
    }

    public function tableName(string $tableAlias): string
    {
        return $this->metadata->get($tableAlias . '.table', $tableAlias);
    }

    public function fields(string $tableAlias): ?array
    {
        return $this->metadata->get($tableAlias . '.fields');
    }

    public function hasField(string $tableAlias, string $field): bool
    {
        $has = $this->metadata->get($tableAlias . '.has');

        return is_array($has) && in_array($field, $has);
    }
}
