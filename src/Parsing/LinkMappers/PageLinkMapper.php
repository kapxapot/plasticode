<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\SlugChunk;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;

/**
 * Page link format: [[page-slug|Text]].
 */
class PageLinkMapper extends EntityLinkMapper
{
    /** @var PageRepositoryInterface */
    private $pageRepository;

    /** @var TagRepositoryInterface */
    private $tagRepository;

    /** @var TagLinkMapper */
    private $tagLinkMapper;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        TagRepositoryInterface $tagRepository,
        RendererInterface $renderer,
        LinkerInterface $linker,
        TagLinkMapper $tagLinkMapper
    )
    {
        parent::__construct($renderer, $linker);

        $this->pageRepository = $pageRepository;
        $this->tagRepository = $tagRepository;
        $this->tagLinkMapper = $tagLinkMapper;
    }

    protected function entity() : string
    {
        return 'page';
    }

    protected function baseUrl() : string
    {
        return $this->linker->page();
    }

    /**
     * Maps page chunks to a page link.
     *
     * @param SlugChunk $slugChunk
     * @param string[] $otherChunks
     * @return string|null
     */
    public function mapSlug(SlugChunk $slugChunk, array $otherChunks) : ?string
    {
        $rawSlug = $slugChunk->slug();
        $content = $otherChunks[0] ?? $rawSlug;

        $slug = Strings::toSlug($rawSlug);

        if (strlen($slug) > 0) {
            $page = $this->pageRepository->getBySlug($slug);

            if ($page && $page->isPublished()) {
                return $this->renderPlaceholder($page->slug, $content);
            }
        }

        // if such tag exists, render as tag
        if ($this->tagLinkMapper && $this->tagRepository->exists($rawSlug)) {
            return $this->renderAsTag($slugChunk, $otherChunks);
        }

        return $this->renderer->noUrl($content, $rawSlug);
    }

    private function renderAsTag(SlugChunk $slugChunk, array $otherChunks) : string
    {
        $slugChunk = $this->tagLinkMapper->adaptSlugChunk($slugChunk);

        return $this->tagLinkMapper->mapSlug($slugChunk, $otherChunks);
    }

    public function renderLinks(ParsingContext $context): ParsingContext
    {
        $context = parent::renderLinks($context);

        $context = $this->tagLinkMapper->renderLinks($context);

        return $context;
    }
}
