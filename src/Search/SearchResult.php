<?php

namespace Plasticode\Search;

use JsonSerializable;
use Plasticode\Collections\Generic\DbModelCollection;
use Plasticode\Interfaces\ArrayableInterface;

class SearchResult implements ArrayableInterface, JsonSerializable
{
    private DbModelCollection $data;
    private int $totalCount;
    private int $filteredCount;

    public function __construct(
        DbModelCollection $data,
        int $totalCount,
        ?int $filteredCount = null
    )
    {
        $this->data = $data;
        $this->totalCount = $totalCount;
        $this->filteredCount = $filteredCount ?? $totalCount;
    }

    public function data(): DbModelCollection
    {
        return $this->data;
    }

    public function totalCount(): int
    {
        return $this->totalCount;
    }

    public function filteredCount(): int
    {
        return $this->filteredCount;
    }

    // JsonSerializable

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    // ArrayableInterface

    public function toArray(): array
    {
        return [
            'data' => $this->data()->serialize(),
            'recordsTotal' => $this->totalCount(),
            'recordsFiltered' => $this->filteredCount(),
        ];
    }
}
