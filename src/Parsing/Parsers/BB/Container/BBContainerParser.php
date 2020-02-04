<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

use Plasticode\Config\Interfaces\BBContainerConfigInterface;
use Plasticode\Parsing\Interfaces\MapperInterface;
use Plasticode\Parsing\Interfaces\MapperSourceInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Webmozart\Assert\Assert;

class BBContainerParser extends BaseStep implements MapperSourceInterface
{
    /** @var BBSequencer */
    private $sequencer;

    /** @var BBTreeBuilder */
    private $treeBuilder;

    /** @var BBTreeRenderer */
    private $treeRenderer;

    /** @var array */
    private $map = [];

    public function __construct(BBContainerConfigInterface $config, BBSequencer $sequencer, BBTreeBuilder $treeBuilder, BBTreeRenderer $treeRenderer)
    {
        $tagMappers = $config->getMappers();

        foreach ($tagMappers as $tag => $mapper) {
            $this->register($tag, $mapper);
        }

        $this->sequencer = $sequencer;
        $this->treeBuilder = $treeBuilder;
        $this->treeRenderer = $treeRenderer;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        if ($context->isEmpty()) {
            return $context;
        }

        $ctags = $this->getTags();
        $sequence = $this->sequencer->getSequence($context->text, $ctags);

        $containerTree = $this->treeBuilder->build($sequence);

        $context->text = $this->treeRenderer->render($containerTree, $this);
        
        return $context;
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
    private function getTags() : array
    {
        return array_keys($this->map);
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
