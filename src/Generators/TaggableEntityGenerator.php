<?php

namespace Plasticode\Generators;

use Plasticode\Generators\Basic\ChangingEntityGenerator;
use Plasticode\Generators\Basic\GeneratorContext;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;
use Respect\Validation\Validator;

abstract class TaggableEntityGenerator extends ChangingEntityGenerator
{
    protected TagRepositoryInterface $tagRepository;

    protected string $tagsField = 'tags';

    public function __construct(
        GeneratorContext $context,
        TagRepositoryInterface $tagRepository
    )
    {
        parent::__construct($context);

        $this->tagRepository = $tagRepository;
    }

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

        $id = $item[$this->idField()];

        $this->tagRepository->deleteByEntity($this->getEntity(), $id);

        foreach ($tags as $tag) {
            $this->tagRepository->store(
                [
                    'entity_type' => $this->getEntity(),
                    'entity_id' => $id,
                    'tag' => $tag,
                ]
            );
        }
    }

    public function afterDelete(array $item) : void
    {
        parent::afterDelete($item);

        $this->tagRepository->deleteByEntity(
            $this->getEntity(),
            $item[$this->idField()]
        );
    }
}
