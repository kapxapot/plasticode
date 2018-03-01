<?php

namespace Plasticode\Core;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Arr;

use Plasticode\IO\File;

class Settings {
	public static function load($path, callable $enrich = null, $entryPoint = 'general.yml') {
		$settings = Yaml::parse(File::load($path . $entryPoint));
		
		foreach ($settings['modules'] as $module) {
			$file = File::load("{$path}{$module}.yml");
			$settings[$module] = Yaml::parse($file);
		}
		
		if (array_key_exists('tables', $settings)) {
			foreach ($settings['tables'] as $table => $tableSettings) {
				$public = $tableSettings['public'] ?? [];
				$private = $tableSettings['private'] ?? [];
				
				$fields = array_unique(array_merge($private, $public));
				
				Arr::set($settings, "tables.{$table}.fields", $fields);
			}
			
			// flatten attributes
			foreach ($settings['entities'] as $entity => $entitySettings) {
				Arr::set($settings, "entities.{$entity}.alias", $entity);
				
				foreach ($entitySettings['columns'] as $column => $columnSettings) {
					foreach ($columnSettings['attributes'] ?? [] as $attr) {
						Arr::set($settings, "entities.{$entity}.columns.{$column}.{$attr}", 'true');
					}
				}
			}
		
			// count sort index
			foreach ($settings['entities'] as $entity => $entitySettings) {
				$sort = Arr::get($settings, "tables.{$entity}.sort");
				
				$count = 0;
				foreach ($entitySettings['columns'] as $column => $columnSettings) {
					if ($column == $sort) {
						Arr::set($settings, "tables.{$entity}.sort_index", $count);
						break;
					}
					elseif (!array_key_exists('hidden', $columnSettings)) {
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
		
		$settings['db'] = [
			'host' => getenv('DB_HOST'),
			'database' => getenv('DB_DATABASE'),
			'user' => getenv('DB_USER'),
			'password' => getenv('DB_PASSWORD'),
		];

		if ($enrich != null) {
			$settings = $enrich($settings);
		}

		return [ 'settings' => $settings ];
	}
}
