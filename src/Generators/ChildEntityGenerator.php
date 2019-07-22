<?php

namespace Plasticode\Generators;

use Plasticode\Util\Pluralizer;

class ChildEntityGenerator extends EntityGenerator
{
    protected $parentName;
    protected $name;
    protected $parentsLabel;
    protected $namesLabel;
    protected $parentNameField;

    public function __construct($container, $entity, $options)
    {
        parent::__construct($container, $entity);
        
        $this->parentName = $options['parent']['name'] ?? 'parent';
        $this->name = $options['child']['name'] ?? 'child';
        $this->parentsLabel = $options['parent']['label'] ?? 'Parents';
        $this->namesLabel = $options['child']['label'] ?? 'Children';
        $this->parentNameField = $options['parent']['name_field'] ?? 'name';
    }

    protected function plural($word)
    {
        return Pluralizer::plural($word);
    }
    
    protected function parents()
    {
        return $this->plural($this->parentName);
    }
    
    protected function names()
    {
        return $this->plural($this->name);
    }
    
    public function getOptions()
    {
        $options = parent::getOptions();
        
        $options['uri'] = $this->parents() . '/{id:\d+}/' . $this->names();
        $options['filter'] = $this->parentName . '_id';
        
        return $options;
    }
    
    public function getAdminParams($args)
    {
        $params = parent::getAdminParams($args);

        $parentId = $args['id'];
        $parent = $this->db->getEntityById($this->parents(), $parentId);

        $params['source'] = $this->parents() . "/{$parentId}/" . $this->names();
        $params['breadcrumbs'] = [
            [ 'text' => $this->parentsLabel, 'link' => $this->router->pathFor('admin.entities.' . $this->parents()) ],
            [ 'text' => $parent[$this->parentNameField] ],
            [ 'text' => $this->namesLabel ],
        ];
        
        $params['hidden'] = [
            $this->parentName . '_id' => $parentId,
        ];
        
        return $params;
    }
}
