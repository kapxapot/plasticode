<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Parsing\ParsingContext;

class ReplacesStep extends BaseStep
{
    /** @var ParsingConfigInterface */
    protected $config;

    public function __construct(ParsingConfigInterface $config)
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
