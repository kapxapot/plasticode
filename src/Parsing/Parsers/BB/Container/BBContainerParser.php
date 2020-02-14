<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

use Plasticode\Parsing\Interfaces\TagMapperSourceInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;

class BBContainerParser extends BaseStep
{
    /** @var TagMapperSourceInterface */
    private $config;

    /** @var BBSequencer */
    private $sequencer;

    /** @var BBTreeBuilder */
    private $treeBuilder;

    /** @var BBTreeRenderer */
    private $treeRenderer;

    public function __construct(TagMapperSourceInterface $config, BBSequencer $sequencer, BBTreeBuilder $treeBuilder, BBTreeRenderer $treeRenderer)
    {
        $this->config = $config;
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

        $ctags = $this->config->getTags();
        $sequence = $this->sequencer->getSequence($context->text, $ctags);

        $tree = $this->treeBuilder->build($sequence);

        $context->text = $this->treeRenderer->render($tree, $this->config);

        // TODO: fluent calls notation
        // $context->text = $this
        //     ->sequencer
        //     ->getSequence($context->text, $ctags)
        //     ->build()
        //     ->render($this);
        
        return $context;
    }
}
