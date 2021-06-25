<?php

namespace Plasticode\Collections\Generic;

use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Models\Interfaces\EquatableInterface;

class EquatableCollection extends TypedCollection
{
    protected string $class = EquatableInterface::class;

    /**
     * Shortcut for from()->distinct().
     * 
     * @return static
     */
    public static function fromDistinct(ArrayableInterface $arrayable): self
    {
        return static::from($arrayable)->distinct();
    }

    /**
     * Returns distinct elements using `contains()` function.
     * 
     * @return static
     */
    public function distinct(): self
    {
        $col = static::make();

        /** @var EquatableInterface */
        foreach ($this as $item) {
            if ($col->contains($item)) {
                continue;
            }

            $col = $col->add($item);
        }

        return $col;
    }

    /**
     * Returns all elements except the specified.
     * 
     * @return static
     */
    public function except(EquatableInterface ...$elements): self
    {
        $toExclude = static::make($elements);

        return $this->where(
            fn (EquatableInterface $eq) => !$toExclude->contains($eq)
        );
    }

    /**
     * Finds an intersection between two collections.
     *
     * @param static $other
     * @return static
     */
    public function intersect(self $other): self
    {
        return static::from(
            $other->where(
                fn (EquatableInterface $e) => $this->contains($e)
            )
        );
    }

    public function contains(?EquatableInterface $element): bool
    {
        if ($element === null) {
            return false;
        }

        return $this->any(
            fn (EquatableInterface $eq) => $eq->equals($element)
        );
    }
}
