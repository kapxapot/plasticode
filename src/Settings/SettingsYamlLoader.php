<?php

namespace Plasticode\Settings;

use Plasticode\IO\File;
use Plasticode\Settings\Interfaces\SettingsLoaderInterface;
use Symfony\Component\Yaml\Yaml;

class SettingsYamlLoader implements SettingsLoaderInterface
{
    private string $path;
    private ?string $entryPoint;

    public function __construct(string $path, ?string $entryPoint = null)
    {
        $this->path = $path;
        $this->entryPoint = $entryPoint ?? 'general.yml';
    }

    public function load(): array
    {
        $entryPointPath = File::combine($this->path, $this->entryPoint);
        $settings = $this->loadFile($entryPointPath);

        $modulesPath = File::combine($this->path, '*.yml');
        $moduleFiles = array_filter(glob($modulesPath), 'is_file');

        foreach ($moduleFiles as $file) {
            if ($file != $this->entryPoint) {
                $module = File::getName($file);
                $settings[$module] = $this->loadFile($file);
            }
        }

        return $settings;
    }

    /**
     * @return mixed
     */
    private function loadFile(string $file)
    {
        $data = File::load($file);
        $data = $this->replaceEnvEntries($data);

        return Yaml::parse($data);
    }

    private function replaceEnvEntries(string $data): string
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
}
