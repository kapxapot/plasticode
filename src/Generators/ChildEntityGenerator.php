<?php

namespace Plasticode\Generators;

use Plasticode\Models\DbModel;
use Plasticode\Util\Pluralizer;
use Psr\Container\ContainerInterface;

/**
 * Currenly not used.
 * 
 * @deprecated 0.6.2
 */
abstract class ChildEntityGenerator extends EntityGenerator
{
    /**
     * Parent entity name.
     */
    protected string $parentName;

    /**
     * Child entity name.
     */
    protected string $name;

    /**
     * Parents label
     */
    protected string $parentsLabel;

    /**
     * Children label.
     */
    protected string $namesLabel;

    /**
     * Parent name field.
     */
    protected string $parentNameField;

    public function __construct(
        ContainerInterface $container,
        string $entity,
        array $options
    )
    {
        parent::__construct(
            $container,
            $entity
        );

        $this->parentName = $options['parent']['name'] ?? 'parent';
        $this->name = $options['child']['name'] ?? 'child';
        $this->parentsLabel = $options['parent']['label'] ?? 'Parents';
        $this->namesLabel = $options['child']['label'] ?? 'Children';
        $this->parentNameField = $options['parent']['name_field'] ?? 'name';
    }

    /**
     * Returns plural form of the word.
     */
    protected function plural(string $word) : string
    {
        return Pluralizer::plural($word);
    }

    /**
     * Returns plural form of parent entity name.
     */
    protected function parents() : string
    {
        return $this->plural($this->parentName);
    }

    /**
     * Returns plural form of child entity name.
     */
    protected function names() : string
    {
        return $this->plural($this->name);
    }

    public function getOptions() : array
    {
        $options = parent::getOptions();

        $options['uri'] = $this->parents() . '/{id:\d+}/' . $this->names();
        $options['filter'] = $this->parentName . '_id';

        return $options;
    }

    public function getAdminParams(array $args) : array
    {
        $params = parent::getAdminParams($args);

        $parentId = $args['id'];
        $parent = $this->getParentById($parentId);

        $params['source'] = $this->parents() . '/' . $parentId . '/' . $this->names();
        $params['breadcrumbs'] = [
            [
                'text' => $this->parentsLabel,
                'link' => $this->router->pathFor(
                    'admin.entities.' . $this->parents()
                )
            ],
            ['text' => $parent[$this->parentNameField]],
            ['text' => $this->namesLabel],
        ];

        $params['hidden'] = [
            $this->parentName . '_id' => $parentId,
        ];

        return $params;
    }

    abstract protected function getParentById(?int $id) : DbModel;
}
