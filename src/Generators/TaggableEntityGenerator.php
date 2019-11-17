<?php

namespace Plasticode\Generators;

use Plasticode\Models\Tag;
use Plasticode\Util\Strings;
use Respect\Validation\Validator;

class TaggableEntityGenerator extends EntityGenerator
{
    protected $tagsField = 'tags';

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);

        $rules[$this->tagsField] = Validator::tags();
        
        return $rules;
    }

    public function afterSave(array $item, array $data) : void
    {
        parent::afterSave($item, $data);

        $tags = Strings::toTags($item[$this->tagsField]);

        $id = $item[$this->idField] ?? null;

        if (!$id) {
            throw new \InvalidArgumentException(
                'Entity id ("' . $this->idField . '" field) must be set.'
            );
        }
        
        Tag::deleteByEntity($this->entity, $id);

        foreach ($tags as $tag) {
            if (strlen($tag) > 0) {
                Tag::store(
                    [
                        'entity_type' => $this->entity,
                        'entity_id' => $id,
                        'tag' => $tag,
                    ]
                );
            }
        }
    }

    public function afterDelete(array $item) : void
    {
        parent::afterDelete($item);
        
        Tag::deleteByEntity($this->entity, $item[$this->idField]);
    }
}
