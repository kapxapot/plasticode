<?php

namespace Plasticode\Core;

use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as v;

class Core
{
    /**
     * Plasticode bootstrap.
     * 
     * Initializes container with provided settings,
     * initializes validation namespaces,
     * initializes database models (they require container).
     *
     * @param bool $withModels Should init models as well?
     */
    public static function bootstrap(
        ContainerInterface $container,
        array $settings,
        array $validationNamespaces = []
    ) : void
    {
        foreach ($settings as $key => $value) {
            $container[$key] = $value;
        }

        v::with('Plasticode\\Validation\\Rules\\');

        foreach ($validationNamespaces as $namespace) {
            v::with($namespace);
        }
    }
}
