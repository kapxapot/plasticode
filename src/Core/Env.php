<?php

namespace Plasticode\Core;

use Dotenv\Dotenv;

class Env
{
    private const DEV = 'dev';
    private const STAGE = 'stage';
    private const PROD = 'prod';

    private string $appEnv;

    private function __construct(string $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    public function is(string $appEnv) : bool
    {
        return $this->appEnv === $appEnv;
    }

    public function isDev() : bool
    {
        return $this->is(self::DEV);
    }

    public function isStage() : bool
    {
        return $this->is(self::STAGE);
    }

    public function isProd() : bool
    {
        return $this->is(self::PROD);
    }

    /**
     * Loads environment variables from .env file.
     */
    public static function load(string $path, ?string $appEnvVar = null) : self
    {
        $appEnvVar = $appEnvVar ?? 'APP_ENV';

        // trying to read .env file
        try {
            $dotenv = new Dotenv($path);
            $dotenv->load();
        }
        catch (\Exception $ex) {
        }

        // APP_ENV must be set either in .env file (dev, stage),
        // or in environment variables (prod)
        $appEnv = getenv($appEnvVar);

        if (strlen($appEnv) == 0) {
            die(
                'Environment variable ' . $appEnvVar . ' can\'t be empty, ' .
                'set it to "dev"/"prod", etc.'
            );
        }

        return new static($appEnv);
    }
}
