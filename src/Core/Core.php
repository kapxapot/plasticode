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
     * Initializes container with provided settings,
     * initializes validation namespaces,
     * initializes database models (they require container).
     *
     * @param ContainerInterface $container
     * @param array $settings
     * @param array $validationNamespaces
     * @param bool $withModels Should init models as well?
     * @return void
     */
    public static function bootstrap(ContainerInterface $container, array $settings, array $validationNamespaces = [], bool $withModels = true)
    {
        foreach ($settings as $key => $value) {
            $container[$key] = $value;
        }
        
        v::with('Plasticode\\Validation\\Rules\\');

        foreach ($validationNamespaces as $namespace) {
            v::with($namespace);
        }
        
        if ($withModels) {
            self::initModels($container);
        }
    }

    public static function initModels($container): void
    {
        Model::init($container);
    }
}
