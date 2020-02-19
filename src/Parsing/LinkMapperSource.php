<?php

namespace Plasticode\Parsing;

use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\Interfaces\LinkMapperSourceInterface;
use Webmozart\Assert\Assert;

abstract class LinkMapperSource implements LinkMapperSourceInterface
{
    /** @var LinkMapperInterface|null */
    private $defaultMapper;

    /** @var LinkMapperInterface[] */
    private $map = [];

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

    public function register(string $tag, LinkMapperInterface $mapper) : void
    {
        Assert::notEmpty($tag);
        Assert::alnum($tag);
        
        $this->map[$tag] = $mapper;
    }

    public function getMapper(string $tag) : LinkMapperInterface
    {
        Assert::true(
            array_key_exists($tag, $this->map),
            'No mapper found for tag \'' . $tag . '\''
        );

        return $this->map[$tag];
    }

    public function getGenericMapper() : ?LinkMapperInterface
    {
        return $this->genericMapper;
    }

    public function setGenericMapper(LinkMapperInterface $mapper) : void
    {
        $this->defaultMapper = $mapper;
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
                array_values($this->map),
                [$this->defaultMapper, $this->genericMapper]
            )
        );
    }
}
