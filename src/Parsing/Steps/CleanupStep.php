<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Text;

class CleanupStep extends BaseStep
{
    /** @var ReplacesConfigInterface */
    protected $config;

    public function __construct(ReplacesConfigInterface $config)
    {
        $this->config = $config;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $context->text = Text::applyRegexReplaces(
            $context->text,
            $this->config->getCleanupReplaces()
        );

        return $context;
    }
}
