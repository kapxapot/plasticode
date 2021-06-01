<?php

namespace Plasticode\Generators\Generic;

use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;
use Respect\Validation\Validator;

abstract class ChangingEntityGenerator extends EntityGenerator
{
    abstract public function getRepository(): ChangingRepositoryInterface;

    protected function getRules(array $data, $id = null): array
    {
        $rules = parent::getRules($data, $id);

        $rules['updated_at'] = Validator::unchanged($this->getRepository(), $id);

        return $rules;
    }
}
