<?php

namespace Plasticode\Generators;

use Plasticode\Util\Pluralizer;
use Psr\Container\ContainerInterface;

abstract class ChildEntityGenerator extends EntityGenerator
{
    /**
     * Parent entity name
     *
     * @var string
     */
    protected $parentName;

    /**
     * Child entity name
     *
     * @var string
     */
    protected $name;

    /**
     * Parents label
     *
     * @var string
     */
    protected $parentsLabel;

    /**
     * Children label
     *
     * @var string
     */
    protected $namesLabel;

    /**
     * Parent name field
     *
     * @var string
     */
    protected $parentNameField;

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
     * Return plural form of the word
     *
     * @param string $word
     * @return string
     */
    protected function plural(string $word) : string
    {
        return Pluralizer::plural($word);
    }
    
    /**
     * Plural parents name
     *
     * @return string
     */
    protected function parents() : string
    {
        return $this->plural($this->parentName);
    }
    
    /**
     * Plural children name
     *
     * @return string
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
        $parent = $this->db->getEntityById($this->parents(), $parentId);

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
}
