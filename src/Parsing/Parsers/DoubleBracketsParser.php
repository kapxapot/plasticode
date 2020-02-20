<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Parsing\Interfaces\LinkMapperSourceInterface;
use Plasticode\Parsing\Interfaces\LinkRendererInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Util\Arrays;

/**
 * Parses double brackets tags such as [[about|About]] page or [[news:123|Some cool news]] news links.
 * 
 * - Default mapper parses [[slug|Content]] links.
 * - Tag mappers parse [[tag:id|Content]] links.
 * - Generic mapper parses all other [[unknown-tag:id|Content]] links.
 * 
 * If there's no matching mapper, the parser leaves the link unchanged.
 * 
 * The mappers can be customized via LinkMapperSourceInterface config ('doubleBracketsConfig' in container).
 */
class DoubleBracketsParser extends BaseStep implements LinkRendererInterface
{
    private const Pattern = '/\[\[(.+)\]\]/U';

    /** @var LinkMapperSourceInterface */
    private $config;

    public function __construct(LinkMapperSourceInterface $config)
    {
        $this->config = $config;
    }

    public function parseContext(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        $context->text = preg_replace_callback(
            self::Pattern,
            function ($matches) {
                [$original, $match] = $matches;
                
                $parsed = $this->parseDoubleBracketsMatch($match);

                return $parsed ?? $original;
            },
            $context->text
        );

        return $context;
    }

    private function parseDoubleBracketsMatch(?string $match) : ?string
    {
        if (strlen($match) == 0) {
            return null;
        }
        
        $text = null;
        $chunks = preg_split('/\|/', $match);

        // looking for ":" in first chunk
        $tagChunk = $chunks[0];
        $tagParts = preg_split('/:/', $tagChunk, null, PREG_SPLIT_NO_EMPTY);
        $tag = $tagParts[0];
        
        // one tag part = default
        if (count($tagParts) == 1) {
            return $this->renderDefault($chunks);
        }

        // many tag parts
        // pattern: [[tag:id|content]]
        // e.g.: [[npc:27412|Слинкин Демогном]]
        $id = $tagParts[1];
        $content = $chunks[1] ?? null;
        $text = $this->renderTag($tag, $chunks);
        
        return strlen($text) > 0
            ? $text
            : null;
    }

    /**
     * Render no-tag link.
     *
     * @param string[] $chunks
     * @return string|null
     */
    private function renderDefault(array $chunks) : ?string
    {
        $mapper = $this->config->getDefaultMapper();

        return $mapper
            ? $mapper->map($chunks)
            : null;
    }

    /**
     * Renders tag link.
     *
     * @param string $tag
     * @param string[] $chunks
     * @return string|null
     */
    private function renderTag(string $tag, array $chunks) : ?string
    {
        $mapper =
            $this->config->getMapper($tag)
            ??
            $this->config->getGenericMapper();

        return $mapper
            ? $mapper->map($chunks)
            : null;
    }

    /**
     * Renders %template% links using registered link renderers.
     * 
     * @param ParsingContext $context
     * @return ParsingContext
     */
    public function renderLinks(ParsingContext $context) : ParsingContext
    {
        $context = clone $context;

        foreach ($this->getLinkRenderers() as $linkRenderer) {
            $context = $linkRenderer->renderLinks($context);
        }

        return $context;
    }

    /**
     * Returns registered link renderers.
     *
     * @return LinkRendererInterface[]
     */
    private function getLinkRenderers() : array
    {
        return Arrays::filter(
            $this->config->getAllMappers(),
            function ($item) {
                return $item instanceof LinkRendererInterface;
            }
        );
    }
}
