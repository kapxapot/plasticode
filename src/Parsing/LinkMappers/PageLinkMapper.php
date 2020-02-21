<?php

namespace Plasticode\Parsing\LinkMappers;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\Interfaces\LinkMapperInterface;
use Plasticode\Parsing\Interfaces\LinkRendererInterface;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\ViewModels\UrlViewModel;

/**
 * Page link format: [[page-slug|Title]].
 */
class PageLinkMapper implements LinkMapperInterface, LinkRendererInterface
{
    /** @var PageRepositoryInterface */
    private $pageRepository;

    /** @var RendererInterface */
    private $renderer;

    /** @var LinkerInterface */
    private $linker;

    public function __construct(PageRepositoryInterface $pageRepository, RendererInterface $renderer, LinkerInterface $linker)
    {
        $this->pageRepository = $pageRepository;
        $this->renderer = $renderer;
        $this->linker = $linker;
    }

    public function map(array $chunks) : ?string
    {
        $slug = $chunks[0];
        $content = $chunks[1] ?? null;

        $page = $this->pageRepository->getBySlug($slug);

        $text = null;

        if ($page && $page->isPublished()) {
            $url = $this->linker->page($slug);
            $viewModel = new UrlViewModel($url, $content);

            return $this->renderer->url($viewModel);
        }

        // if such tag exists, render as tag
        if (Tag::exists($id)) {
            return $this->renderer->tag renderTag($id, $name);
        }

        return $text ?? $this->renderer->noArticleUrl($name, $id, $cat);
    }
}
