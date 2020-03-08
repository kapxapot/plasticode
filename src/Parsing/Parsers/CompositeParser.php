<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Parsing\Interfaces\LinkRendererInterface;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Util\Arrays;
use Webmozart\Assert\Assert;

class CompositeParser extends BaseStep implements ParserInterface
{
    /** @var ParsingStepInterface[] */
    private $pipeline;

    /**
     * Creates composite parser without any steps by default.
     *
     * @param ParsingStepInterface[]|null $pipeline
     */
    public function __construct(?array $pipeline = null)
    {
        $this->setPipeline($pipeline ?? []);
    }

    /**
     * Sets parsing steps pipeline.
     *
     * @param ParsingStepInterface[] $pipeline
     * @return self
     */
    public function setPipeline(array $pipeline) : self
    {
        Assert::allIsInstanceOf($pipeline, ParsingStepInterface::class);

        $this->pipeline = $pipeline;
        return $this;
    }

    /**
     * Executes all parsing steps on the contex one-by-one.
     */
    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        foreach ($this->pipeline as $step) {
            $context = $step->parseContext($context);
        }

        return $context;
    }

    /**
     * Renders links using pipeline's link renderers.
     *
     * @param ParsingContext $context
     * @return ParsingContext
     */
    public function renderLinks(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        foreach ($this->getLinkRenderers() as $linkRenderer) {
            $context = $linkRenderer->renderLinks($context);
        }

        return $context;
    }

    /**
     * Returns pipeline link renderers.
     *
     * @return LinkRendererInterface[]
     */
    private function getLinkRenderers() : array
    {
        return Arrays::filter(
            $this->pipeline,
            function ($item) {
                return $item instanceof LinkRendererInterface;
            }
        );
    }
}
