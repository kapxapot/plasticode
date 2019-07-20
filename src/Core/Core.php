<?php

namespace Plasticode\Core;

use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as v;

use Plasticode\Models\Model;

class Core
{
    /**
     * Plasticode bootstrap
     *
     * @param ContainerInterface $container
     * @param array $settings
     * @return void
     */
    public static function bootstrap(ContainerInterface $container, array $settings) : void
    {
        foreach ($settings as $key => $value) {
            $container[$key] = $value;
        }
        
        v::with('Plasticode\\Validation\\Rules\\');
        v::with('App\\Validation\\Rules\\'); // refactor this, this shouldn't be here
        
        Model::init($container);
    }
}
