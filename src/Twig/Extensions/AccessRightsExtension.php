<?php

namespace Plasticode\Twig\Extensions;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AccessRightsExtension extends AbstractExtension
{
    private Access $access;
    private AuthInterface $auth;

    public function __construct(
        Access $access,
        AuthInterface $auth
    )
    {
        $this->access = $access;
        $this->auth = $auth;
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
        return $this->access->checkRights(
            $entity,
            $action,
            $this->auth->getUser()
        );
    }
}
