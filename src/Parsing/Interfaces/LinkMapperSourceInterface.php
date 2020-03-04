<?php

namespace Plasticode\Parsing\Interfaces;

interface LinkMapperSourceInterface
{
    /**
     * Returns mapper for no tag.
     *
     * @return LinkMapperInterface|null
     */
    public function getDefaultMapper() : ?LinkMapperInterface;

    /**
     * Sets mapper for no tag.
     *
     * @param LinkMapperInterface $mapper
     * @return void
     */
    public function setDefaultMapper(LinkMapperInterface $mapper) : void;

    /**
     * Registers tagged mapper.
     *
     * @param TaggedLinkMapperInterface $mapper
     * @return void
     */
    public function registerTaggedMapper(TaggedLinkMapperInterface $mapper) : void;

    /**
     * Registers many tagged mappers.
     *
     * @param TaggedLinkMapperInterface[] $mappers
     * @return void
     */
    public function registerTaggedMappers(array $mappers) : void;

    /**
     * Returns tagged mapper for the tag.
     *
     * @param string $tag
     * @return TaggedLinkMapperInterface|null
     */
    public function getTaggedMapper(string $tag) : ?TaggedLinkMapperInterface;

    /**
     * Returns mapper for non-specified tags.
     *
     * @return LinkMapperInterface|null
     */
    public function getGenericMapper() : ?LinkMapperInterface;

    /**
     * Sets mapper for non-specified tags.
     *
     * @param LinkMapperInterface $mapper
     * @return void
     */
    public function setGenericMapper(LinkMapperInterface $mapper) : void;

    /**
     * Returns all mappers, including default & generic mappers.
     *
     * @return LinkMapperInterface[]
     */
    public function getAllMappers() : array;
}
