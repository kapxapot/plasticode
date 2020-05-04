<?php

namespace Plasticode\Collections\Basic;

use Plasticode\Models\Interfaces\DbModelInterface;
use Webmozart\Assert\Assert;

class DbModelCollection extends TypedCollection
{
    protected string $class = DbModelInterface::class;

    protected function __construct(?array $data)
    {
        Assert::subclassOf($this->class, DbModelInterface::class);

        parent::__construct($data);
    }

    /**
     * Returns distinct values by class name and id.
     * Also cleans nulls (they don't make sense).
     * 
     * @return static
     */
    public function distinct() : self
    {
        return
            parent::distinctBy(
                fn (?DbModelInterface $m) =>
                $m
                    ? get_class($m) . '_' . $m->getId()
                    : ''
            )
            ->clean();
    }
}
