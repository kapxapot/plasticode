<?php

namespace Plasticode\Traits;

use Plasticode\Models\Basic\DbModel;
use Webmozart\Assert\Assert;

trait EntityRelated
{
    /**
     * Full entity class name, must be a subclass of {@see DbModel}.
     */
    abstract protected function entityClass() : string;

    /**
     * Returns entity class name and checks that it is a subclass of {@see DbModel}.
     */
    protected function getEntityClass() : string
    {
        Assert::subclassOf($this->entityClass(), DbModel::class);

        return $this->entityClass();
    }

    /**
     * Returns the id field name of the related entity.
     * Usually it's 'id'.
     */
    protected function idField() : string
    {
        $entityClass = $this->getEntityClass();
        return $entityClass::idField();
    }

    /**
     * Returns entity alias (in snake case) based on the entity class name.
     * 
     * ArticleCategory -> article_categories.
     */
    protected function pluralAlias() : string
    {
        $entityClass = $this->getEntityClass();

        return $entityClass::pluralAlias();
    }
}
