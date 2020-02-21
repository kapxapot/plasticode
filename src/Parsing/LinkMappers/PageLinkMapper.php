<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;
use Webmozart\Assert\Assert;

/**
 * Page link format: [[page-slug|Text]].
 */
class PageLinkMapper extends EntityLinkMapper
{
    /** @var PageRepositoryInterface */
    private $pageRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var TagLinkMapper|null */
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

    protected $entity = 'page';

    protected function baseUrl() : string
    {
        return $this->linker->page();
    }

    /**
     * Maps page chunks to a page link.
     *
     * @param string[] $chunks
     * @return string|null
     */
    public function map(array $chunks) : ?string
    {
        Assert::notEmpty($chunks);

        $rawSlug = $chunks[0];
        $content = $chunks[1] ?? $rawSlug;

        $slug = Strings::toSlug($rawSlug);

        if (strlen($slug) > 0) {
            $page = $this->pageRepository->getBySlug($slug);

            if ($page && $page->isPublished()) {
                return $this->renderPlaceholder($page->slug, $content);
            }
        }

        // if such tag exists, render as tag
        if ($this->tagLinkMapper && $this->tagRepository->exists($rawSlug)) {
            return $this->renderAsTag($chunks);
        }

        return $this->renderer->noUrl($content, $rawSlug);
    }

    private function renderAsTag(array $chunks) : string
    {
        $rawSlug = $chunks[0];
        $chunks[0] = $this->tagLinkMapper->tagChunk($rawSlug);

        return $this->tagLinkMapper->map($chunks);
    }
}
