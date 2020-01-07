<?php

namespace Plasticode\Parsing;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class CompositeParser extends BaseStep
{
    /** @var ParsingConfigInterface */
    protected $config;

    /** @var RendererInterface */
    protected $renderer;

    /** @var ParsingStepInterface[] */
    private $pipeline = [];

    public function __construct(ParsingConfigInterface $config, RendererInterface $renderer)
    {
        $this->config = $config;
        $this->renderer = $renderer;
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
     * TODO: This function must be extracted into a separate parser.
     * 
     * Cuts the already parsed text by [cut] tag and inserts the link to full text.
     * Otherwise just removes the [cut] tag.
     */
    public function parseCut(string $text, string $url = null, bool $full = true, string $label = null) : string
    {
        if (!$full) {
            Assert::stringNotEmpty(
                $url,
                'Non-empty url required for parseCut() in short mode.'
            );
        }

        $cut = Text::Cut;
        $cutpos = strpos($text, $cut);

        if ($cutpos !== false) {
            if ($full) {
                $text = str_replace($cut, '', $text);
                $text = Text::brsToPs($text);
            } else {
                $text = substr($text, 0, $cutpos);
                $text = Text::trimBrs($text);
                
                $text .= $this->renderer->component(
                    'read_more',
                    ['url' => $url, 'label' => $label]
                );
            }

            $text = Text::applyRegexReplaces(
                $text,
                $this->config->getCleanupReplaces()
            );
        }

        return $text;
    }

    /**
     * Executes all parsing steps on the contex one-by-one.
     */
    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        foreach ($this->pipeline as $step) {
            $context = $step->parse($context);
        }

        return $context;
    }

    /**
     * TODO: This function must be extracted into a separate parser.
     * 
     * Override this to render placeholder links (double brackets, etc.).
     * 
     * Example:
     * $text = str_replace('%news%/', $this->linker->news(), $text);
     */
    public function renderLinks(ParsingContext $context) : ParsingContext
    {
        return clone $context;
    }
}
