<?php

namespace Plasticode\Parsing;

use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\Interfaces\LinkMapperSourceInterface;
use Plasticode\Parsing\Interfaces\TaggedLinkMapperInterface;

class LinkMapperSource implements LinkMapperSourceInterface
{
    /** @var TaggedLinkMapperInterface[] */
    private array $taggedMappers = [];

    private ?LinkMapperInterface $defaultMapper = null;
    private ?LinkMapperInterface $genericMapper = null;

    public function registerTaggedMapper(TaggedLinkMapperInterface $mapper) : void
    {
        $tag = $mapper->tag();
        $this->taggedMappers[$tag] = $mapper;
    }

    public function registerTaggedMappers(array $mappers) : void
    {
        foreach ($mappers as $mapper) {
            $this->registerTaggedMapper($mapper);
        }
    }

    public function getTaggedMapper(string $tag) : ?TaggedLinkMapperInterface
    {
        return $this->taggedMappers[$tag] ?? null;
    }

    public function getDefaultMapper() : ?LinkMapperInterface
    {
        return $this->defaultMapper;
    }

    public function setDefaultMapper(LinkMapperInterface $mapper) : void
    {
        $this->defaultMapper = $mapper;
    }

    public function getGenericMapper() : ?LinkMapperInterface
    {
        return $this->genericMapper;
    }

    public function setGenericMapper(LinkMapperInterface $mapper) : void
    {
        $this->genericMapper = $mapper;
    }

    /**
     * Returns all mappers, including default & generic mappers.
     *
     * @return LinkMapperInterface[]
     */
    public function getAllMappers() : array
    {
        return array_filter(
            [
                ...array_values($this->taggedMappers),
                $this->defaultMapper,
                $this->genericMapper
            ]
        );
    }
}
