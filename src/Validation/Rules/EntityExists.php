<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Repositories\Interfaces\Generic\GetRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class EntityExists extends AbstractRule
{
    private GetRepositoryInterface $repository;

    public function __construct(
        GetRepositoryInterface $repository
    )
    {
        $this->repository = $repository;
    }

    public function validate($input)
    {
        $entity = $this->repository->get($input);

        return $entity !== null;
    }
}
