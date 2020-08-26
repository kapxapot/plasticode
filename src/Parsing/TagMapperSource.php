<?php

namespace Plasticode\Parsing;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Interfaces\TagMapperSourceInterface;
use Webmozart\Assert\Assert;

abstract class TagMapperSource implements TagMapperSourceInterface
{
    /** @var array<string, TagMapperInterface> */
    private array $map = [];

    /** @var array<string, string> */
    private array $componentMap = [];

    public function register(
        string $tag,
        TagMapperInterface $mapper,
        ?string $componentName = null
    ) : void
    {
        Assert::notEmpty($tag);
        Assert::alnum($tag);

        $this->map[$tag] = $mapper;

        if (strlen($componentName) > 0) {
            $this->componentMap[$tag] = $componentName;
        }
    }

    /**
     * Returns registered tags.
     *
     * @return string[]
     */
    public function getTags() : array
    {
        return array_keys($this->map);
    }

    /**
     * Get mapper for the tag. Null if absent.
     */
    public function getMapper(string $tag) : ?TagMapperInterface
    {
        return $this->map[$tag] ?? null;
    }

    public function getComponentName(string $tag) : string
    {
        return $this->componentMap[$tag] ?? $tag;
    }
}
