<?php

namespace Plasticode\Collections\Basic;

use Plasticode\Models\Interfaces\DbModelInterface;

class DbModelCollection extends TypedCollection
{
    protected string $class = DbModelInterface::class;

    /**
     * Returns distinct values by class name and id.
     * 
     * @return static
     */
    public function distinct() : self
    {
        return $this->distinctBy(
            fn (DbModelInterface $m) => get_class($m) . $m->getId()
        );
    }

    /**
     * Extracts ids of models.
     */
    public function ids() : ScalarCollection
    {
        return $this
            ->map(
                fn (DbModelInterface $m) => $m->getId()
            )
            ->toScalarCollection();
    }
}
