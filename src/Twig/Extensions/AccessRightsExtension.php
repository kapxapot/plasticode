<?php

namespace Plasticode\Twig\Extensions;

use Plasticode\Auth\Access;

class AccessRightsExtension extends \Twig_Extension
{
    /**
     * Access rights
     *
     * @var Plasticode\Auth\Access
     */
    private $access;

    public function __construct(Access $access)
    {
        $this->access = $access;
    }
    
    public function getName() : string
    {
        return 'accessRights';
    }
    
    public function getFunctions() : array
    {
        return [
            new \Twig_SimpleFunction('can', array($this, 'can')),
        ];
    }
    
    public function can(string $entity, string $action) : bool
    {
        $can = !$action;

        if (!$can) {
            $can = $this->access->checkRights($entity, $action);
        }
        
        return $can;
    }
}
