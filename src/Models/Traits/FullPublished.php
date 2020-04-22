<?php

namespace Plasticode\Models\Traits;

use Plasticode\Util\Date;

/**
 * Full publish support: published + published_at.
 */
trait FullPublished
{
    use Published
    {
        publish as protected parentPublish;
        isPublished as protected parentIsPublished;
    }

    public function publish()
    {
        $this->parentPublish();

        if (is_null($this->publishedAt)) {
            $this->publishedAt = Date::dbNow();
        }
    }

    public function isPublished() : bool
    {
        return $this->parentIsPublished()
            && $this->publishedAt
            && Date::happened($this->publishedAt);
    }

    public function publishedAtIso() : string
    {
        return $this->publishedAt ? Date::iso($this->publishedAt) : null;
    }
}
