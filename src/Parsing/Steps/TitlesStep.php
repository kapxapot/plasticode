<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\ContentsItem;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\TitlesContext;

class TitlesStep extends BaseStep
{
    private const MIN_LEVEL = 2;
    private const MAX_LEVEL = 6;

    /** @var RendererInterface */
    private $renderer;

    /** @var ParsingStepInterface */
    private $lineParser;

    /**
     * @param ParsingStepInterface $lineParser Parser of [] and [[]] brackets
     */
    public function __construct(RendererInterface $renderer, ParsingStepInterface $lineParser)
    {
        $this->renderer = $renderer;
        $this->lineParser = $lineParser;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
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
        $parsedContent = $this->lineParser->parse($content);

        $contentsLine = new ContentsItem($level - 1, $label, $parsedContent->text);

        if ($withContents) {
            $context->addContents($contentsLine);
        }

        return $this->renderer->component('subtitle', $contentsLine);
    }
}
