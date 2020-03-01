<?php

namespace Plasticode\Parsing;

use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\Interfaces\LinkMapperSourceInterface;
use Plasticode\Parsing\Interfaces\TaggedLinkMapperInterface;

class LinkMapperSource implements LinkMapperSourceInterface
{
    /** @var LinkMapperInterface|null */
    private $defaultMapper;

    /** @var TaggedLinkMapperInterface[] */
    private $taggedMappers = [];

    /** @var LinkMapperInterface|null */
    private $genericMapper;

    public function getDefaultMapper() : ?LinkMapperInterface
    {
        return $this->defaultMapper;
    }

    public function setDefaultMapper(LinkMapperInterface $mapper) : void
    {
        $this->defaultMapper = $mapper;
    }

    public function registerTaggedMapper(TaggedLinkMapperInterface $mapper) : void
    {
        $tag = $mapper->tag();
        $this->taggedMappers[$tag] = $mapper;
    }

    public function getTaggedMapper(string $tag) : ?TaggedLinkMapperInterface
    {
        return $this->taggedMappers[$tag] ?? null;
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
            array_merge(
                array_values($this->taggedMappers),
                [$this->defaultMapper, $this->genericMapper]
            )
        );
    }
}
