<?php

namespace Plasticode\Core;

use Dotenv\Dotenv;

class Env
{
    public static function load(string $path, string $appEnvVar = null)
    {
        $appEnvVar = $appEnvVar ?? 'APP_ENV';

        // trying to read .env file
        try {
            $dotenv = new Dotenv($path);
            $dotenv->load();
        }
        catch (\Exception $ex) {
        }
        
        // APP_ENV must be set either in .env file (dev, stage), or in environment variables (prod)
        if (strlen(getenv($appEnvVar)) == 0) {
            die('Environment variable ' . $appEnvVar . ' can\'t be empty, set it to "dev"/"prod", etc.');
        }
    }
}
