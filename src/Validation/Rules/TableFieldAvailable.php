<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Repositories\Interfaces\Basic\FieldValidatingRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

abstract class TableFieldAvailable extends AbstractRule
{
    /** @var callable fn (mixed) : bool */
    private $validateField;

    public function __construct(
        FieldValidatingRepositoryInterface $repository,
        string $field,
        ?int $exceptId = null
    )
    {
        $this->validateField = fn ($input) => $repository->isValidField(
            $field,
            $input,
            $exceptId
        );
    }

    /**
     * @param mixed $input
     */
    public function validate($input)
    {
        return ($this->validateField)($input);
    }
}
