<?php

namespace Plasticode\Repositories\Idiorm\Traits;

use Plasticode\Data\Query;

/**
 * Implements {@see \Plasticode\Repositories\Interfaces\Generic\FieldValidatingRepositoryInterface}.
 */
trait FieldValidatingRepository
{
    /**
     * @param mixed $value
     */
    public function isValidField(string $field, $value, ?int $exceptId = null): bool
    {
        $query = $this
            ->query()
            ->where($field, $value);

        if ($exceptId > 0) {
            $query = $query->whereNotEqual($this->idField(), $exceptId);
        }

        return $query->count() == 0;
    }

    abstract protected function query(): Query;

    abstract protected function idField(): string;
}
