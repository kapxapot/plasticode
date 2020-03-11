<?php

namespace Plasticode\Twig\Extensions;

use Plasticode\Auth\Access;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AccessRightsExtension extends AbstractExtension
{
    /** @var Access */
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
            new TwigFunction('can', [$this, 'can']),
        ];
    }
    
    public function can(string $entity, string $action) : bool
    {
        return $this->access->checkRights($entity, $action);
    }
}
