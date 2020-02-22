<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\EntityLinkMapperInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\SlugChunk;
use Webmozart\Assert\Assert;

abstract class EntityLinkMapper implements EntityLinkMapperInterface
{
    /**
     * @var string Entity name
     */
    protected $entity;

    /** @var RendererInterface */
    protected $renderer;

    /** @var LinkerInterface */
    protected $linker;

    public function __construct(RendererInterface $renderer, LinkerInterface $linker)
    {
        $this->renderer = $renderer;
        $this->linker = $linker;
    }

    private function checkEntity() : void
    {
        Assert::notNull($this->entity, 'Entity name is not specified.');
    }

    protected function renderPlaceholder(string $slug, string $text) : string
    {
        $this->checkEntity();

        $url = '%' . $this->entity . '%/' . $slug;
        
        return $this->renderer->entityUrl($url, $text);
    }

    public function renderLinks(ParsingContext $context): ParsingContext
    {
        $this->checkEntity();

        $context = clone $context;

        $context->text = preg_replace(
            '/%' . $this->entity . '%\//',
            $this->baseUrl(),
            $context->text
        );

        return $context;
    }

    protected abstract function baseUrl() : string;

    public static function toSlugChunk(string $slugChunk) : SlugChunk
    {
        $parts = preg_split('/:/', $slugChunk, null, PREG_SPLIT_NO_EMPTY);

        return count($parts) > 1
            ? new SlugChunk($parts[0], $parts[1])
            : new SlugChunk(null, $parts[0]);
    }

    public function tagChunk(string $slug) : string
    {
        $this->checkEntity();

        return $this->entity . ':' . $slug;
    }
}
