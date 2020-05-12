<?php

namespace Plasticode\Testing\Seeders;

use Plasticode\Models\Role;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class RoleSeeder implements ArraySeederInterface
{
    /**
     * @return Role[]
     */
    public function seed() : array
    {
        return [
            new Role(
                [
                    'id' => 1,
                    'tag' => 'admin',
                ]
            ),
            new Role(
                [
                    'id' => 2,
                    'tag' => 'editor',
                ]
            ),
            new Role(
                [
                    'id' => 3,
                    'tag' => 'author',
                ]
            ),
        ];
    }
}
