<?php

namespace Plasticode\Settings\Transformations;

use Plasticode\Config\Config;
use Plasticode\Interfaces\ArrayTransformationInterface;

/**
 * Adds "root_dir" setting.
 * 
 * Ugly, but why not...
 */
class RootDirTransformation implements ArrayTransformationInterface
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function transformArray(array $data): array
    {
        $data[Config::ROOT_DIR_PATH] = $this->rootDir;

        return $data;
    }
}
