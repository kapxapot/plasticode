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
     * Registers entity mapper for a tag.
     *
     * @param string $tag
     * @param EntityLinkMapperInterface $mapper
     * @return void
     */
    public function registerEntityMapper(string $tag, EntityLinkMapperInterface $mapper) : void;

    /**
     * Returns entity mapper for a tag.
     *
     * @param string $tag
     * @return EntityLinkMapperInterface
     */
    public function getEntityMapper(string $tag) : EntityLinkMapperInterface;

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
