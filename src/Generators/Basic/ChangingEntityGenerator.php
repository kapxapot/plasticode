<?php

namespace Plasticode\Generators\Basic;

use Plasticode\Repositories\Interfaces\Basic\ChangingRepositoryInterface;
use Respect\Validation\Validator;

abstract class ChangingEntityGenerator extends EntityGenerator
{
    abstract protected function getRepository() : ChangingRepositoryInterface;

    protected function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);

        $rules['updated_at'] = Validator::unchanged($this->getRepository(), $id);

        return $rules;
    }
}
