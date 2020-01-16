<?php

namespace Plasticode\Parsing\Parsers\BB;

use Plasticode\Config\Interfaces\BBContainerConfigInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\MapperInterface;
use Webmozart\Assert\Assert;

class BBContainer
{
    /** @var RendererInterface */
    private $renderer;

    /** @var array */
    private $map = [];

    public function __construct(BBContainerConfigInterface $config, RendererInterface $renderer)
    {
        $tagMappers = $config->getMappers();

        foreach ($tagMappers as $tag => $mapper) {
            $this->register($tag, $mapper);
        }

        $this->renderer = $renderer;
    }

    public function register(string $tag, MapperInterface $mapper) : void
    {
        Assert::notEmpty($tag, 'Tag can\'t be empty.');
        Assert::alnum($tag, 'Tag can contain only alphanumeric characters.');
        Assert::notNull($mapper, 'Mapper can\'t be null.');

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

    public function isKnownTag(string $tag) : bool
    {
        return array_key_exists($tag, $this->map);
    }
    
    public function renderNode(BBNode $node) : string
    {
        $tag = $node->tag;
        $mapper = $this->getMapper($tag);

        return $this->renderer->component(
            $tag,
            $mapper->map($node->text, $node->attributes)
        );
    }

    private function getMapper(string $tag) : MapperInterface
    {
        Assert::true(
            $this->isKnownTag($tag),
            'No mapper found for BB container tag \'' . $tag . '\''
        );

        return $this->map[$tag];
    }
}
