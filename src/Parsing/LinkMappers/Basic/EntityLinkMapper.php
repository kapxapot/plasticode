<?php

namespace Plasticode\Parsing\LinkMappers\Basic;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\EntityLinkMapperInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\SlugChunk;
use Webmozart\Assert\Assert;

/**
 * Entity link mapper.
 */
abstract class EntityLinkMapper extends SlugLinkMapper implements EntityLinkMapperInterface
{
    /** @var RendererInterface */
    protected $renderer;

    /** @var LinkerInterface */
    protected $linker;

    public function __construct(RendererInterface $renderer, LinkerInterface $linker)
    {
        $this->renderer = $renderer;
        $this->linker = $linker;

        Assert::notEmpty($this->entity(), 'Entity is not specified.');
        Assert::alnum($this->entity());
    }

    protected abstract function entity() : string;

    protected function renderPlaceholder(string $slug, string $text) : string
    {
        $url = '%' . $this->entity() . '%/' . $slug;
        
        return $this->renderer->entityUrl($url, $text);
    }

    protected abstract function baseUrl() : string;

    /**
     * Adapts the provided slug chunk to the current entity's slug chunk.
     *
     * @param SlugChunk $slugChunk
     * @return SlugChunk
     */
    public function adaptSlugChunk(SlugChunk $slugChunk) : SlugChunk
    {
        return new SlugChunk(
            $this->entity(),
            $slugChunk->slug()
        );
    }

    public function renderLinks(ParsingContext $context): ParsingContext
    {
        $context = clone $context;

        $context->text = preg_replace(
            '/%' . $this->entity() . '%\//',
            $this->baseUrl(),
            $context->text
        );

        return $context;
    }
}
