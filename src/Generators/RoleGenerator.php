<?php

namespace Plasticode\Generators;

use Plasticode\Models\Role;
use Plasticode\Repositories\Interfaces\RoleRepositoryInterface;

class RoleGenerator extends EntityGenerator
{
    private RoleRepositoryInterface $roleRepository;

    public function __construct(
        GeneratorContext $context,
        RoleRepositoryInterface $roleRepository
    )
    {
        parent::__construct($context);

        $this->roleRepository = $roleRepository;
    }

    protected function entityClass() : string
    {
        return Role::class;
    }
}
