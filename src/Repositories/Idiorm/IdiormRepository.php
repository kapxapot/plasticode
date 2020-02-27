<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Data\Db;
use Plasticode\Data\Rights;
use Plasticode\Repositories\Interfaces\RepositoryInterface;
use Plasticode\Util\Classes;
use Plasticode\Util\Pluralizer;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

abstract class IdiormRepository implements RepositoryInterface
{
    /**
     * Table name
     *
     * @var string
     */
    protected static $table;

    /** @var Db */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function can(string $rights) : bool
    {
        return $this->tableRights()->can($rights);
    }

    private function tableRights() : Rights
    {
        return $this->db->getTableRights(
            $this->getTable()
        );
    }

    /**
     * Repository MUST be named as '{entity_class}Repository'.
     * The table name is generated as a plural form of 'entity_class'.
     * 
     * Alternatively, the table name can be specified explicitly in static $table var.
     *
     * @return string
     */
    public function getTable() : string
    {
        if (strlen(static::$table) > 0) {
            return static::$table;
        }

        // \Plasticode\..\ArticleCategoryRepository
        // -> ArticleCategoryRepository
        $class = Classes::shortName(static::class);

        $suffix = 'Repository';

        Assert::true(Strings::endsWith($class, $suffix));

        // ArticleCategoryRepository -> ArticleCategory
        $entityClass = Strings::trimEnd($class, $suffix);

        // ArticleCategory -> ArticleCategories
        $entityPlural = Pluralizer::plural($entityClass);

        // ArticleCategories -> article_categories
        $table = Strings::toSnakeCase($entityPlural);

        return $table;
    }
}
