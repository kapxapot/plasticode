<?php

namespace Plasticode\Parsing;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Util\Text;
use Webmozart\Assert\Assert;

class CompositeParser
{
    /** @var \Plasticode\Config\Interfaces\ParsingConfigInterface */
    protected $config;

    /** @var \Plasticode\Core\Interfaces\RendererInterface */
    protected $renderer;

    /** @var \Plasticode\Parsing\Interfaces\ParsingStepInterface[] */
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
     * @param \Plasticode\Parsing\Interfaces\ParsingStepInterface[] $pipeline
     * @return self
     */
    public function setPipeline(array $pipeline) : self
    {
        $this->pipeline = $pipeline;
        return $this;
    }
    
    /**
     * Cuts the already parsed text by [cut] tag and inserts the link to full text.
     * Otherwise just removes the [cut] tag.
     *
     * @param string $text
     * @param string $url
     * @param boolean $full
     * @param string $label
     * @return string
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
     * Parses text, returns parsing context.
     * 
     * This parsing is not final.
     *
     * @param string|null $text
     * @return ParsingContext|null
     */
    public function parse(?string $text) : ?ParsingContext
    {
        if (strlen($text) == 0) {
            return null;
        }

        $context = ParsingContext::fromText($text);

        foreach ($this->pipeline as $step) {
            $context = $step->parse($context);
        }

        return $context;
    }

    /**
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
