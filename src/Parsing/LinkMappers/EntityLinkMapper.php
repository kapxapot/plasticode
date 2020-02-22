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

    public function entity() : string
    {
        $this->validateEntity();
        return $this->entity;
    }

    private function validateEntity() : void
    {
        Assert::notEmpty($this->entity, 'Entity name is not specified.');
        Assert::alnum($this->entity);
    }

    protected function renderPlaceholder(string $slug, string $text) : string
    {
        $url = '%' . $this->entity() . '%/' . $slug;
        
        return $this->renderer->entityUrl($url, $text);
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
        return $this->entity() . ':' . $slug;
    }
}
