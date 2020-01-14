<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;

class CompositeParser extends BaseStep
{
    /** @var ParsingConfigInterface */
    protected $config;

    /** @var RendererInterface */
    protected $renderer;

    /** @var ParsingStepInterface[] */
    private $pipeline;

    /**
     * Creates composite parser without any steps by default.
     *
     * @param ParsingConfigInterface $config
     * @param RendererInterface $renderer
     * @param ParsingStepInterface[]|null $pipeline
     */
    public function __construct(ParsingConfigInterface $config, RendererInterface $renderer, ?array $pipeline = null)
    {
        $this->config = $config;
        $this->renderer = $renderer;

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
