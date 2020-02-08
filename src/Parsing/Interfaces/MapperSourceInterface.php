<?php

namespace Plasticode\Parsing\Interfaces;

interface MapperSourceInterface
{
    /**
     * Registers new mapper for a specified $tag.
     *
     * @param string $tag
     * @param MapperInterface $mapper
     * @return void
     */
    public function register(string $tag, MapperInterface $mapper) : void;
    
    /**
     * Returns registered tags.
     *
     * @return string[]
     */
    public function getTags() : array;
    
    /**
     * Returns all registered mappers as an associative array.
     * $tag => $mapper.
     *
     * @return array
     */
    public function getMappers() : array;
    
    /**
     * Returns mapper by $tag.
     *
     * @param string $tag
     * @return MapperInterface
     */
    public function getMapper(string $tag) : MapperInterface;
}
