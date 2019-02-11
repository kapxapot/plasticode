<?php

namespace Plasticode\Generators;

use Plasticode\Models\Tag;
use Plasticode\Util\Strings;

class TaggableEntityGenerator extends EntityGenerator
{
	protected $tagsField = 'tags';

	public function afterSave($item, $data)
	{
		$tags = Strings::toTags($item->{$this->tagsField});
	
		if (!($item->id > 0)) {
			throw new \InvalidArgumentException('Entity id must be positive');
		}
		
		Tag::deleteByEntity($this->entity, $item->id);

    	foreach ($tags as $tag) {
    		if (strlen($tag) > 0) {
    			$t = Tag::create();
                
        		$t->entityType = $this->entity;
                $t->entityId = $item->id;
                $t->tag = $tag;
                
        		$t->save();
    		}
    	}
	}

	public function afterDelete($item)
	{
	    parent::afterDelete($item);
	    
	    Tag::deleteByEntity($this->entity, $item->id);
	}
}
