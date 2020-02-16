<?php

namespace Plasticode\Parsing;

use Plasticode\Parsing\Interfaces\TagMapperInterface;
use Plasticode\Parsing\Interfaces\TagMapperSourceInterface;
use Webmozart\Assert\Assert;

abstract class TagMapperSource implements TagMapperSourceInterface
{
    /** @var array */
    private $map = [];

    private $componentMap = [];

    public function register(string $tag, TagMapperInterface $mapper, ?string $componentName = null) : void
    {
        Assert::notEmpty($tag);
        Assert::alnum($tag);
        Assert::notNull($mapper);

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

    public function getMapper(string $tag) : TagMapperInterface
    {
        Assert::true(
            array_key_exists($tag, $this->map),
            'No mapper found for BB container tag \'' . $tag . '\''
        );

        return $this->map[$tag];
    }

    public function getComponentName(string $tag): string
    {
        return $this->componentMap[$tag] ?? $tag;
    }
}
