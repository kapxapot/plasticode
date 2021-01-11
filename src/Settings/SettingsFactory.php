<?php

namespace Plasticode\Settings;

use Plasticode\IO\File;
use Plasticode\Settings\Transformations\RootDirTransformation;
use Plasticode\Settings\Transformations\TablesTransformation;
use Plasticode\Settings\Transformations\WebTransformation;

class SettingsFactory
{
    public static function make(string $rootDir, ?string $path = null): array
    {
        return SettingsBuilder::build(
            new SettingsYamlLoader(
                File::combine($rootDir, $path ?? 'settings')
            ),
            new TablesTransformation(),
            new WebTransformation(),
            new RootDirTransformation($rootDir)
        );
    }
}
