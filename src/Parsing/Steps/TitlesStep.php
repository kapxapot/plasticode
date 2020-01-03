<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Collection;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\ContentsItem;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Parser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\TitlesContext;

class TitlesStep implements ParsingStepInterface
{
    private const MIN_LEVEL = 2;
    private const MAX_LEVEL = 6;

    /** @var \Plasticode\Core\Interfaces\RendererInterface */
    private $renderer;

    /** @var \Plasticode\Parsing\Parser */
    private $lineParser;

    /**
     * @param Parser $lineParser Parser of [] and [[]] brackets
     */
    public function __construct(RendererInterface $renderer, Parser $lineParser)
    {
        $this->renderer = $renderer;
        $this->lineParser = $lineParser;
    }

    public function parse(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $titlesContext = new TitlesContext(self::MIN_LEVEL, self::MAX_LEVEL);

        $parsedLines = $this->parseLines(
            $context->getLines(),
            $titlesContext
        );
        
        $context->setLines($parsedLines);
        $context->contents = $titlesContext->getContents();
        
        return $context;
    }

    private function parseLines(array $lines, TitlesContext &$context) : array
    {
        $results = [];

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (strlen($line) > 0) {
                $line = preg_replace_callback(
                    $this->getPattern(),
                    function ($matches) use (&$context) {
                        return $this->parseLine($matches, $context);
                    },
                    $line
                );
            }

            $results[] = $line;
        }

        return $results;
    }

    private function getPattern() : string
    {
        $r = '{' . self::MIN_LEVEL . ',' . self::MAX_LEVEL . '}';
        return '/^(\|' . $r . '|#' . $r . '\s+)(.*)$/';
    }

    private function parseLine(array $matches, TitlesContext &$context) : string
    {
        $sticks = trim($matches[1]);
        $content = trim($matches[2], ' |');
        
        $withContents = true;

        if (substr($content, -1) == '#') {
            $withContents = false;
            $content = rtrim($content, ' #');
        }
        
        $level = strlen($sticks);
        $label = null;

        if ($withContents) {
            $context->incCount($level);

            $label = implode(
                ContentsItem::LABEL_DELIMITER,
                $context->getCountSlice($level)
            );
        }
        
        // parse brackets (!)
        $content = $this->lineParser->parse($content);

        $contentsLine = new ContentsItem($level - 1, $label, $content->text);

        if ($withContents) {
            $context->addContents($contentsLine);
        }

        return $this->renderer->component('subtitle', $contentsLine);
    }
}
