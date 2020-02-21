<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Models\Page;
use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\Interfaces\LinkRendererInterface;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;
use Plasticode\Util\Strings;
use Plasticode\ViewModels\UrlViewModel;

/**
 * Page link format: [[page-slug|Title]].
 */
class PageLinkMapper implements LinkMapperInterface, LinkRendererInterface
{
    /** @var PageRepositoryInterface */
    private $pageRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var RendererInterface */
    private $renderer;

    /** @var LinkerInterface */
    private $linker;

    /** @var LinkMapperInterface */
    private $tagLinkMapper;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        TagRepositoryInterface $tagRepository,
        RendererInterface $renderer,
        LinkerInterface $linker,
        LinkMapperInterface $tagLinkMapper
    )
    {
        $this->pageRepository = $pageRepository;
        $this->tagRepository = $tagRepository;
        $this->renderer = $renderer;
        $this->linker = $linker;
        $this->tagLinkMapper = $tagLinkMapper;
    }

    /**
     * Maps page chunks to a 
     *
     * @param array $chunks
     * @return string|null
     */
    public function map(array $chunks) : ?string
    {
        $rawSlug = $chunks[0];
        $content = $chunks[1] ?? $rawSlug;

        $slug = Strings::toSlug($rawSlug);

        if (strlen($slug) > 0) {
            $page = $this->pageRepository->getBySlug($slug);

            if ($page && $page->isPublished()) {
                return $this->renderPageUrl($page, $content);
            }
        }

        // if such tag exists, render as tag
        if ($this->tagRepository->exists($rawSlug)) {
            return $this->renderAsTag($chunks);
        }

        return $this->renderer->noUrl($content, $rawSlug);
    }

    private function renderPageUrl(Page $page, string $content) : ?string
    {
        $url = $this->linker->page($page);
        $viewModel = new UrlViewModel($url, $content);

        return $this->renderer->url($viewModel);
    }

    private function renderAsTag(array $chunks) : string
    {
        $chunks[0] = 'tag:' . $chunks[0];

        return $this->tagLinkMapper->map($chunks);
    }
}
