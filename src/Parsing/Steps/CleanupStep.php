<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Text;

class CleanupStep implements ParsingStepInterface
{
    /** @var \Plasticode\Config\Interfaces\ParsingConfigInterface */
    protected $config;

    public function __construct(ParsingConfigInterface $config)
    {
        $this->config = $config;
    }

    public function parse(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $context->text = Text::applyRegexReplaces(
            $context->text,
            $this->config->getCleanupReplaces()
        );

        return $context;
    }
}
