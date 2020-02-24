<?php

namespace Plasticode\Parsing;

use Plasticode\Parsing\Interfaces\EntityLinkMapperInterface;
use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\Interfaces\LinkMapperSourceInterface;

class LinkMapperSource implements LinkMapperSourceInterface
{
    /** @var LinkMapperInterface|null */
    private $defaultMapper;

    /** @var EntityLinkMapperInterface[] */
    private $entityMappers = [];

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

    public function registerEntityMapper(EntityLinkMapperInterface $mapper) : void
    {
        $tag = $mapper->entity();
        $this->entityMappers[$tag] = $mapper;
    }

    public function getEntityMapper(string $tag) : ?EntityLinkMapperInterface
    {
        return $this->entityMappers[$tag] ?? null;
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
                array_values($this->entityMappers),
                [$this->defaultMapper, $this->genericMapper]
            )
        );
    }
}
