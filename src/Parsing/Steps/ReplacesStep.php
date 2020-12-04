<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Parsing\ParsingContext;

class ReplacesStep extends BaseStep
{
    protected ReplacesConfigInterface $config;

    public function __construct(ReplacesConfigInterface $config)
    {
        $this->config = $config;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $replaces = $this->config->getReplaces();

        foreach ($replaces as $from => $to) {
            $context->text = str_replace($from, $to, $context->text);
        }

        return $context;
    }
}
