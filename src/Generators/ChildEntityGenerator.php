<?php

namespace Plasticode\Generators;

use Plasticode\Util\Pluralizer;
use Psr\Container\ContainerInterface;

class ChildEntityGenerator extends EntityGenerator
{
    protected $parentName;
    protected $name;
    protected $parentsLabel;
    protected $namesLabel;
    protected $parentNameField;

    public function __construct(ContainerInterface $container, string $entity, array $options)
    {
        parent::__construct($container, $entity);
        
        $this->parentName = $options['parent']['name'] ?? 'parent';
        $this->name = $options['child']['name'] ?? 'child';
        $this->parentsLabel = $options['parent']['label'] ?? 'Parents';
        $this->namesLabel = $options['child']['label'] ?? 'Children';
        $this->parentNameField = $options['parent']['name_field'] ?? 'name';
    }

    protected function plural(string $word) : string
    {
        return Pluralizer::plural($word);
    }
    
    protected function parents() : string
    {
        return $this->plural($this->parentName);
    }
    
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
