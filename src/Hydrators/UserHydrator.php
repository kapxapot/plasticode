<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class UserHydrator extends Hydrator
{
    private RoleRepositoryInterface $roleRepository;
    private LinkerInterface $linker;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        LinkerInterface $linker
    )
    {
        $this->roleRepository = $roleRepository;
        $this->linker = $linker;
    }

    /**
     * @param User $entity
     */
    public function hydrate(DbModel $entity) : User
    {
        return $entity
            ->withRole(
                fn () => $this->roleRepository->get($entity->roleId)
            )
            ->withGravatarUrl(
                fn () =>
                $this->linker->gravatarUrl(
                    $entity->gravatarHash()
                )
            );
    }
}
