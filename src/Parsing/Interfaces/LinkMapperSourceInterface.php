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
     * Registers mapper for a tag.
     *
     * @param string $tag
     * @param LinkMapperInterface $mapper
     * @return void
     */
    public function register(string $tag, LinkMapperInterface $mapper) : void;

    /**
     * Returns mapper for a tag.
     *
     * @param string $tag
     * @return LinkMapperInterface
     */
    public function getMapper(string $tag) : LinkMapperInterface;

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
