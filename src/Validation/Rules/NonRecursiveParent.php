<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Repositories\Interfaces\Basic\ParentedRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class NonRecursiveParent extends AbstractRule
{
    /** @var callable fn (mixed) : bool */
    private $isNonRecursive;

    public function __construct(ParentedRepositoryInterface $repository, ?int $id = null)
    {
        $this->isNonRecursive = function ($parentId) use ($repository, $id) : bool {
            $item = $repository->get($id);

            return is_null($item) || !$item->isRecursiveParent($parentId);
        };
    }

    /**
     * @param mixed $input
     */
    public function validate($input)
    {
        return ($this->isNonRecursive)($input);
    }
}
