<?php

namespace Plasticode\Collections\Generic;

use Plasticode\Models\Interfaces\DbModelInterface;

class DbModelCollection extends TypedCollection
{
    protected string $class = DbModelInterface::class;

    /**
     * Returns distinct values by class name and id.
     * 
     * @return static
     */
    public function distinct(): self
    {
        return $this->distinctBy(
            fn (DbModelInterface $m) => get_class($m) . $m->getId()
        );
    }

    /**
     * Returns the next id (max id + 1) based on the current items.
     * 
     * For repository mocks.
     */
    public function nextId(): int
    {
        $maxId = $this->ids()->max() ?? 0;

        return $maxId + 1;
    }

    /**
     * Extracts the ids of the models.
     */
    public function ids(): ScalarCollection
    {
        return $this->scalarize(
            fn (DbModelInterface $m) => $m->getId()
        );
    }

    public function contains(DbModelInterface $model): bool
    {
        return $this->anyFirst(
            fn (DbModelInterface $m) => $m->equals($model)
        );
    }
}
