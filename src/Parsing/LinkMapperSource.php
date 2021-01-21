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

    public function registerTaggedMapper(TaggedLinkMapperInterface $mapper): self
    {
        $tag = $mapper->tag();
        $this->taggedMappers[$tag] = $mapper;

        return $this;
    }

    public function registerTaggedMappers(TaggedLinkMapperInterface ...$mappers): self
    {
        foreach ($mappers as $mapper) {
            $this->registerTaggedMapper($mapper);
        }

        return $this;
    }

    public function getTaggedMapper(string $tag): ?TaggedLinkMapperInterface
    {
        return $this->taggedMappers[$tag] ?? null;
    }

    public function getDefaultMapper(): ?LinkMapperInterface
    {
        return $this->defaultMapper;
    }

    public function setDefaultMapper(LinkMapperInterface $mapper): self
    {
        $this->defaultMapper = $mapper;

        return $this;
    }

    public function getGenericMapper(): ?LinkMapperInterface
    {
        return $this->genericMapper;
    }

    public function setGenericMapper(LinkMapperInterface $mapper): self
    {
        $this->genericMapper = $mapper;

        return $this;
    }

    public function getAllMappers(): array
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
