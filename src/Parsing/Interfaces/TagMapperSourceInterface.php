<?php

namespace Plasticode\Parsing\Interfaces;

interface TagMapperSourceInterface
{
    /**
     * Registers new mapper for a specified $tag.
     */
    function register(string $tag, TagMapperInterface $mapper) : void;

    /**
     * Returns registered tags.
     *
     * @return string[]
     */
    function getTags() : array;

    /**
     * Get mapper for the tag. Null if absent.
     */
    function getMapper(string $tag) : ?TagMapperInterface;

    /**
     * Returns component name for $tag. Default = $tag.
     */
    function getComponentName(string $tag) : string;
}
