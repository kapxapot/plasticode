<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Repositories\Interfaces\Generic\ChangingRepositoryInterface;
use Plasticode\Repositories\Interfaces\Generic\FilteringRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class Unchanged extends AbstractRule
{
    /** @var callable fn (mixed): bool */
    private $isUnchanged;

    public function __construct(ChangingRepositoryInterface $repository, ?int $id = null)
    {
        $this->isUnchanged = function ($input) use ($repository, $id): bool {
            $item = $repository->get($id);

            $updatedAt = $repository instanceof FilteringRepositoryInterface
                ? $item->updatedAtIso()
                : $item->updatedAt;

            return $item === null || $updatedAt === $input;
        };
    }

    /**
     * @param mixed $input
     */
    public function validate($input)
    {
        return ($this->isUnchanged)($input);
    }
}
