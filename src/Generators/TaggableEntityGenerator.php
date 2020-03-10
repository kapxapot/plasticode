<?php

namespace Plasticode\Generators;

use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator;
use Webmozart\Assert\Assert;

abstract class TaggableEntityGenerator extends EntityGenerator
{
    /** @var TagRepositoryInterface */
    protected $tagRepository;

    protected $tagsField = 'tags';

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->tagRepository = $container->tagRepository;
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

        $id = $item[$this->idField] ?? null;

        Assert::notNull(
            $id,
            'Entity id ("' . $this->idField . '" field) must be set.'
        );

        $this->tagRepository->deleteByEntity($this->entity, $id);

        foreach ($tags as $tag) {
            if (strlen($tag) > 0) {
                $this->tagRepository->store(
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
        
        $this->tagRepository->deleteByEntity($this->entity, $item[$this->idField]);
    }
}
