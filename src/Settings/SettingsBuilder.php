<?php

namespace Plasticode\Settings;

use Plasticode\Interfaces\ArrayTransformationInterface;
use Plasticode\Settings\Interfaces\SettingsLoaderInterface;

/**
 * Loads and transforms settings.
 */
class SettingsBuilder
{
    public static function build(
        SettingsLoaderInterface $loader,
        ArrayTransformationInterface ...$transformations
    ): array
    {
        $data = $loader->load();

        foreach ($transformations as $transformation) {
            $data = $transformation->transformArray($data);
        }

        return $data;
    }
}
