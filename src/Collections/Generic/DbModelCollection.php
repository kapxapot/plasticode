<?php

namespace Plasticode\Collections\Generic;

use Plasticode\Models\Interfaces\DbModelInterface;
use Plasticode\Models\Interfaces\SerializableInterface;

class DbModelCollection extends EquatableCollection implements SerializableInterface
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
    public function ids(): NumericCollection
    {
        return $this
            ->where(
                fn (DbModelInterface $m) => $m->getId() !== null
            )
            ->numerize(
                fn (DbModelInterface $m) => $m->getId()
            );
    }

    public function serialize(): array
    {
        return $this
            ->map(
                fn (DbModelInterface $m) => $m->serialize()
            )
            ->toArray();
    }
}
