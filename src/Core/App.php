<?php

namespace Plasticode\Core;

use Slim\App as SlimApp;

class App
{
    public static function get(array $appSettings) : SlimApp
    {
        return new SlimApp($appSettings);
    }
}
