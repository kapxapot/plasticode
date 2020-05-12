<?php

namespace Plasticode\Testing\Seeders;

use Plasticode\Models\User;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class UserSeeder implements ArraySeederInterface
{
    private RoleRepositoryInterface $roleRepository;

    public function __construct(
        RoleRepositoryInterface $roleRepository
    )
    {
        $this->roleRepository = $roleRepository;
    }

    /**
     * @return User[]
     */
    public function seed() : array
    {
        return [
            (new User(
                [
                    'id' => 1,
                    'name' => 'Gorge Brugilio',
                    'login' => 'admino',
                    'role_id' => 1,
                ]
            ))
            ->withRole($this->roleRepository->get(1)),
            (new User(
                [
                    'id' => 2,
                    'name' => 'Andrea Gonzalez',
                    'login' => 'editress',
                    'role_id' => 2,
                ]
            ))
            ->withRole($this->roleRepository->get(2)),
            (new User(
                [
                    'id' => 3,
                    'name' => 'Santa Claus',
                    'login' => 'noob',
                    'role_id' => 3,
                ]
            ))
            ->withRole($this->roleRepository->get(3)),
        ];
    }
}
