<?php

namespace Plasticode\Models\Interfaces;

interface PageInterface extends NewsSourceInterface
{
    public function isPublished(): bool;

    public function getSlug(): string;
}
