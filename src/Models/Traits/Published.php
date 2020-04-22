<?php

namespace Plasticode\Models\Traits;

/**
 * Limited publish support: only published (no published_at).
 */
trait Published
{
    public function publish()
    {
        $this->published = 1;
    }

    public function isPublished() : bool
    {
        return $this->published == 1;
    }
}
