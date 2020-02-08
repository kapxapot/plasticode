<?php

namespace Plasticode\Config;

use Plasticode\Parsing\Interfaces\MapperInterface;
use Plasticode\Parsing\Interfaces\MapperSourceInterface;
use Plasticode\Parsing\Mappers\ListMapper;
use Plasticode\Parsing\Mappers\QuoteMapper;
use Plasticode\Parsing\Mappers\SpoilerMapper;
use Webmozart\Assert\Assert;

class BBContainerConfig implements MapperSourceInterface
{
    /** @var array */
    private $map = [];

    public function __construct()
    {
        $this->register('spoiler', new SpoilerMapper());
        $this->register('list', new ListMapper());
        $this->register('quote', new QuoteMapper());
    }

    public function register(string $tag, MapperInterface $mapper) : void
    {
        Assert::notEmpty($tag);
        Assert::alnum($tag);
        Assert::notNull($mapper);

        $this->map[$tag] = $mapper;
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

    public function getMappers() : array
    {
        return $this->map;
    }

    public function getMapper(string $tag) : MapperInterface
    {
        Assert::true(
            array_key_exists($tag, $this->map),
            'No mapper found for BB container tag \'' . $tag . '\''
        );

        return $this->map[$tag];
    }
}
