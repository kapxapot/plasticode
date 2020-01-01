<?php

namespace Plasticode\Parsing\Steps;

use Plasticode\Collection;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\ContentsItem;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Parser;
use Plasticode\Parsing\ParsingContext;

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

        $contents = Collection::makeEmpty();

        $lines = $context->getLines();
        $lines = $this->parseLines($lines, $contents);
        
        $context->setLines($lines);
        $context->contents = $contents;
        
        return $context;
    }

    private function parseLines(array $lines, Collection &$contents) : array
    {
        $results = [];
        $count = $this->initCount();

        foreach ($lines as $line) {
            $line = trim($line);
            
            if (strlen($line) > 0) {
                $line = preg_replace_callback(
                    $this->getPattern(),
                    function ($matches) use (&$contents, &$count) {
                        return $this->parseLine($matches, $contents, $count);
                    },
                    $line
                );
            }

            $results[] = $line;
        }

        return $results;
    }

    private function initCount() : array
    {
        $count = [];

        for ($i = self::MIN_LEVEL; $i <= self::MAX_LEVEL; $i++) {
            $count[$i] = 0;
        }

        return $count;
    }

    private function getPattern() : string
    {
        $r = '{' . self::MIN_LEVEL . ',' . self::MAX_LEVEL . '}';
        return '/^(\|' . $r . '|#' . $r . '\s+)(.*)$/';
    }

    private function parseLine(array $matches, Collection &$contents, array &$count) : string
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
            $count[$level]++;
            
            for ($i = $level + 1; $i <= self::MAX_LEVEL; $i++) {
                $count[$i] = 0;
            }

            $label = implode(
                ContentsItem::LABEL_DELIMITER,
                array_slice($count, 0, $level - self::MIN_LEVEL + 1)
            );
        }
        
        // parse brackets (!)
        $content = $this->lineParser->parse($content);

        $contentsLine = new ContentsItem($level - 1, $label, $content->text);

        if ($withContents) {
            $contents = $contents->add($contentsLine);
        }

        return $this->renderer->component('subtitle', $contentsLine);
    }
}
