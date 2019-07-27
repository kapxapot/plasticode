<?php

namespace Plasticode\Core;

use Plasticode\Models\Model;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as v;

class Core
{
    /**
     * Plasticode bootstrap
     *
     * @param ContainerInterface $container
     * @param array $settings
     * @param array $validationNamespaces
     * @return void
     */
    public static function bootstrap(ContainerInterface $container, array $settings, array $validationNamespaces = [])
    {
        foreach ($settings as $key => $value) {
            $container[$key] = $value;
        }
        
        v::with('Plasticode\\Validation\\Rules\\');

        foreach ($validationNamespaces as $namespace) {
            v::with($namespace);
        }
        
        Model::init($container);
    }
}
