<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Data\Db;
use Plasticode\Models\User;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

class UserRepository extends IdiormRepository implements UserRepositoryInterface
{
    protected $entityClass = User::class;

    private RoleRepositoryInterface $roleRepository;
    private LinkerInterface $linker;

    public function __construct(
        Db $db,
        RoleRepositoryInterface $roleRepository,
        LinkerInterface $linker
    )
    {
        parent::__construct($db);

        $this->roleRepository = $roleRepository;
        $this->linker = $linker;
    }

    protected function ormObjToEntity(\ORM $ormObj) : User
    {
        /** @var User */
        $user = parent::ormObjToEntity($ormObj);

        return $user
            ->withRole(
                $this->roleRepository->get($user->roleId)
            )
            ->withGravatarUrl(
                $this->linker->gravatarUrl(
                    $user->gravatarHash()
                )
            );
    }

    public function get(int $id) : ?User
    {
        return $this->getEntity($id);
    }

    public function create(array $data) : User
    {
        return $this->createEntity($data);
    }

    public function save(User $user) : User
    {
        return $this->saveEntity($user);
    }

    public function getByLogin(string $login) : ?User
    {
        return $this
            ->query()
            ->whereAnyIs(
                [
                    ['login' => $login],
                    ['email' => $login],
                ]
            )
            ->one();
    }
}
