<?php

namespace Plasticode\Parsing\Parsers;

use Plasticode\Config\Parsing\DoubleBracketsConfig;
use Plasticode\Parsing\Interfaces\LinkRendererInterface;
use Plasticode\Parsing\LinkMappers\Generic\SlugLinkMapper;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\BaseStep;
use Plasticode\Util\Arrays;

/**
 * Parses double brackets tags such as [[about|About]] page
 * or [[news:123|Some cool news]] news links.
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
    private const PATTERN = '/\[\[(.+)\]\]/U';
    private const CHUNK_DELIMITER_PATTERN = '/\|/';

    private DoubleBracketsConfig $config;

    public function __construct(DoubleBracketsConfig $config)
    {
        $this->config = $config;
    }

    public function parseContext(ParsingContext $context): ParsingContext
    {
        $context = clone $context;

        $context->text = preg_replace_callback(
            self::PATTERN,
            function ($matches) {
                [$original, $match] = $matches;
                
                $parsed = $this->parseDoubleBracketsMatch($match);

                return $parsed ?? $original;
            },
            $context->text
        );

        return $context;
    }

    private function parseDoubleBracketsMatch(?string $match): ?string
    {
        $chunks = preg_split(self::CHUNK_DELIMITER_PATTERN, $match);
        $chunks = Arrays::trim($chunks);

        if (empty($chunks)) {
            return null;
        }

        $slug = SlugLinkMapper::toSlugChunk($chunks[0]);

        if (!$slug->hasTag()) {
            return $this->renderDefault($chunks);
        }

        return $this->renderTag($slug->tag(), $chunks);
    }

    /**
     * Render no-tag link.
     *
     * @param string[] $chunks
     */
    private function renderDefault(array $chunks): ?string
    {
        $mapper = $this->config->getDefaultMapper();

        return $mapper
            ? $mapper->map($chunks)
            : null;
    }

    /**
     * Renders tag link.
     *
     * @param string[] $chunks
     */
    private function renderTag(string $tag, array $chunks): ?string
    {
        $mapper =
            $this->config->getTaggedMapper($tag)
            ?? $this->config->getGenericMapper();

        return $mapper
            ? $mapper->map($chunks)
            : null;
    }

    /**
     * Renders %template% links using registered link renderers.
     */
    public function renderLinks(ParsingContext $context): ParsingContext
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
    private function getLinkRenderers(): array
    {
        return Arrays::filter(
            $this->config->getAllMappers(),
            fn ($item) => $item instanceof LinkRendererInterface
        );
    }
}
