<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Hydrators\Interfaces\HydratorInterface;
use Plasticode\Models\DbModel;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class UserHydrator implements HydratorInterface
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
    protected function hydrate(DbModel $entity) : User
    {
        return $entity
            ->withRole(
                $this->roleRepository->get($entity->roleId)
            )
            ->withGravatarUrl(
                $this->linker->gravatarUrl(
                    $entity->gravatarHash()
                )
            );
    }
}
