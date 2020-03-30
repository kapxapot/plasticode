<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Util\Date;

/**
 * Full publish support: published + published_at.
 */
trait FullPublished
{
    use Published
    {
        Published::wherePublished as protected parentWherePublished;
        publish as protected parentPublish;
        isPublished as protected parentIsPublished;
    }

    protected static function wherePublished(Query $query) : Query
    {
        return self::parentWherePublished($query)
            ->whereRaw('(published_at < now())');
    }

    public function publish()
    {
        $this->parentPublish();

        if ($this->publishedAt === null) {
            $this->publishedAt = Date::dbNow();
        }
    }

    public function isPublished() : bool
    {
        return $this->parentIsPublished() && Date::happened($this->publishedAt);
    }

    public function publishedAtIso() : string
    {
        return $this->publishedAt ? Date::iso($this->publishedAt) : null;
    }
}