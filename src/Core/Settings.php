<?php

namespace Plasticode\Core;

use Plasticode\IO\File;
use Plasticode\Util\Arrays;
use Symfony\Component\Yaml\Yaml;

class Settings
{
    private static function loadFile(string $file)
    {
        $data = File::load($file);
        $data = self::replaceEnvEntries($data);

        return Yaml::parse($data);
    }

    private static function replaceEnvEntries(string $data) : string
    {
        return preg_replace_callback(
            '/\{(\w+)\}/',
            function ($matches) {
                $var = $matches[1];
                $env = getenv($var);

                return ($env !== false) ? $env : '';
            },
            $data
        );
    }

    public static function load(
        string $path,
        \Closure $enrich = null,
        string $entryPoint = null
    ) : array
    {
        $entryPoint = $entryPoint ?? 'general.yml';

        $entryPointPath = File::combine($path, $entryPoint);
        $settings = self::loadFile($entryPointPath);

        $modulesPath = File::combine($path, '*.yml');
        $moduleFiles = array_filter(glob($modulesPath), 'is_file');

        foreach ($moduleFiles as $file) {
            if ($file != $entryPoint) {
                $module = File::getName($file);
                $settings[$module] = self::loadFile($file);
            }
        }

        if (array_key_exists('tables', $settings)) {
            // merge public + private => fields
            foreach ($settings['tables'] as $table => $tableSettings) {
                $public = $tableSettings['public'] ?? [];
                $private = $tableSettings['private'] ?? [];

                $fields = array_unique(array_merge($private, $public));
                Arrays::set($settings, "tables.{$table}.fields", $fields);
            }

            // flatten attributes
            foreach ($settings['entities'] as $entity => $entitySettings) {
                Arrays::set($settings, "entities.{$entity}.alias", $entity);

                foreach ($entitySettings['columns'] as $column => $columnSettings) {
                    foreach ($columnSettings['attributes'] ?? [] as $attr) {
                        Arrays::set($settings, "entities.{$entity}.columns.{$column}.{$attr}", 'true');
                    }
                }
            }

            // count sort index
            foreach ($settings['entities'] as $entity => $entitySettings) {
                $sort = Arrays::get($settings, "tables.{$entity}.sort");

                $count = 0;

                foreach ($entitySettings['columns'] as $column => $columnSettings) {
                    if ($column == $sort) {
                        Arrays::set($settings, "tables.{$entity}.sort_index", $count);
                        break;
                    } elseif (!array_key_exists('hidden', $columnSettings)) {
                        $count++;
                    }
                }
            }
        }

        $root = $settings['folders']['root'];
        $settings['root'] = $root;

        $settings['api'] = $root . ($settings['folders']['api'] ?? '/api/v1/');

        $rootSafe = str_replace('/', '', $root);

        if (strlen($rootSafe) > 0) {
            $settings['auth_token_key'] = $rootSafe . '_auth_token';
        }

        if ($enrich != null) {
            $settings = $enrich($settings);
        }

        return ['settings' => $settings];
    }
}
