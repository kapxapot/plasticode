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
     */
    function setDefaultMapper(LinkMapperInterface $mapper): void;

    /**
     * Registers tagged mapper.
     */
    function registerTaggedMapper(TaggedLinkMapperInterface $mapper): void;

    /**
     * Registers many tagged mappers.
     */
    function registerTaggedMappers(TaggedLinkMapperInterface ...$mappers): void;

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
     */
    function setGenericMapper(LinkMapperInterface $mapper): void;

    /**
     * Returns all mappers, including default & generic mappers.
     *
     * @return LinkMapperInterface[]
     */
    function getAllMappers(): array;
}
