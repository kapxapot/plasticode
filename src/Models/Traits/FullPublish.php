<?php

namespace Plasticode\Models\Traits;

use Plasticode\Models\DbModel;
use Plasticode\Query;
use Plasticode\Util\Date;

/**
 * Full publish support: published + published_at.
 */
trait FullPublish
{
    use Publish
    {
        Publish::wherePublished as protected parentWherePublished;
        publish as protected parentPublish;
        isPublished as protected parentIsPublished;
    }

    /**
     * Looks for a protected record by id.
     *
     * @param int|string $id
     * @return null|DbModel
     */
    public static function findProtected($id) : ?DbModel
    {
        return self::getProtected()->find($id);
    }

    public static function getProtected() : Query
    {
        $query = self::query();
        $editor = self::can('edit');
        
        if ($editor) {
            return $query;
        }

        $published = '(published = 1 and published_at < now())';
        $user = self::getCurrentUser();

        if ($user) {
            return $query->whereRaw('(' . $published . ' or created_by = ?)', [$user->getId()]);
        }
        
        return $query->whereRaw($published);
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
