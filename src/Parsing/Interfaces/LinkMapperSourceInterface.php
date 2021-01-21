<?php

namespace Plasticode\Parsing\Interfaces;

interface LinkMapperSourceInterface
{
    /**
     * Returns mapper for no tag.
     */
    function getDefaultMapper(): ?LinkMapperInterface;

    /**
     * Sets mapper for no tag.
     * 
     * @return $this
     */
    function setDefaultMapper(LinkMapperInterface $mapper): self;

    /**
     * Registers tagged mapper.
     * 
     * @return $this
     */
    function registerTaggedMapper(TaggedLinkMapperInterface $mapper): self;

    /**
     * Registers many tagged mappers.
     * 
     * @return $this
     */
    function registerTaggedMappers(TaggedLinkMapperInterface ...$mappers): self;

    /**
     * Returns tagged mapper for the tag.
     */
    function getTaggedMapper(string $tag): ?TaggedLinkMapperInterface;

    /**
     * Returns mapper for non-specified tags.
     */
    function getGenericMapper(): ?LinkMapperInterface;

    /**
     * Sets mapper for non-specified tags.
     * 
     * @return $this
     */
    function setGenericMapper(LinkMapperInterface $mapper): self;

    /**
     * Returns all mappers, including default & generic mappers.
     *
     * @return LinkMapperInterface[]
     */
    function getAllMappers(): array;
}
