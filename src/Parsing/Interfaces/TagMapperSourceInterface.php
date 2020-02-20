<?php

namespace Plasticode\Parsing\Interfaces;

interface TagMapperSourceInterface
{
    /**
     * Registers new mapper for a specified $tag.
     *
     * @param string $tag
     * @param TagMapperInterface $mapper
     * @return void
     */
    public function register(string $tag, TagMapperInterface $mapper) : void;
    
    /**
     * Returns registered tags.
     *
     * @return string[]
     */
    public function getTags() : array;
    
    /**
     * Get mapper for the tag. Null if absent.
     *
     * @param string $tag
     * @return TagMapperInterface|null
     */
    public function getMapper(string $tag) : ?TagMapperInterface;

    /**
     * Returns component name for $tag. Default = $tag.
     *
     * @param string $tag
     * @return string
     */
    public function getComponentName(string $tag) : string;
}
