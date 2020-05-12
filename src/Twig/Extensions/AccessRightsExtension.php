<?php

namespace Plasticode\Twig\Extensions;

use Plasticode\Auth\Access;
use Plasticode\Auth\Interfaces\AuthInterface;
use Plasticode\Models\Interfaces\DbModelInterface;
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
            new TwigFunction('can_entity', [$this, 'canEntity']),
        ];
    }

    public function can(string $table, string $action) : bool
    {
        return $this->access->checkActionRights(
            $table,
            $action,
            $this->auth->getUser()
        );
    }

    /**
     * Checks entity rights.
     */
    public function canEntity(DbModelInterface $entity, string $action) : bool
    {
        $alias = $entity::pluralAlias();

        $rights = $this->access->getTableRights(
            $alias,
            $this->auth->getUser()
        );

        return $rights->canEntity($entity->toArray(), $action);
    }
}
