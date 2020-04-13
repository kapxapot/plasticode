<?php

namespace Plasticode\Hydrators;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\External\Gravatar;
use Plasticode\Hydrators\Basic\Hydrator;
use Plasticode\Models\DbModel;
use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class UserHydrator extends Hydrator
{
    private RoleRepositoryInterface $roleRepository;
    private LinkerInterface $linker;
    private Gravatar $gravatar;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        LinkerInterface $linker,
        Gravatar $gravatar
    )
    {
        $this->roleRepository = $roleRepository;
        $this->linker = $linker;
        $this->gravatar = $gravatar;
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
                    $this->gravatar->hash(
                        $entity->email
                    )
                )
            );
    }
}
