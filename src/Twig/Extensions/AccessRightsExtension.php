<?php

namespace Plasticode\Twig\Extensions;

class AccessRightsExtension extends \Twig_Extension {
    private $container;

    public function __construct($container) {
        $this->container = $container;
    }
    
    public function getName() {
        return "accessRights";
    }
    
    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('can', array($this, 'can')),
        ];
    }
    
    public function can($entity, $action) {
        $can = !$action;
        if (!$can) {
            $access = $this->container->access;
            $can = $access->checkRights($entity, $action);
        }
        
        return $can;
    }
}
