<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Webmozart\Assert\Assert;

class CompositeParser extends BaseStep
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

    public function addStep(ParsingStepInterface $step) : self
    {
        $this->pipeline[] = $step;
        return $this;
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
}
