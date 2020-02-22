<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Parsing\LinkMappers\NewsLinkMapper;
use Plasticode\Parsing\LinkMappers\PageLinkMapper;
use Plasticode\Parsing\LinkMappers\TagLinkMapper;
use Plasticode\Parsing\LinkMapperSource;
use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Plasticode\Repositories\Interfaces\TagRepositoryInterface;

class DoubleBracketsConfig extends LinkMapperSource
{
    public function __construct(
        PageRepositoryInterface $pageRepository,
        TagRepositoryInterface $tagRepository,
        RendererInterface $renderer,
        LinkerInterface $linker
    )
    {
        $tagLinkMapper = new TagLinkMapper($renderer, $linker);

        $pageLinkMapper = new PageLinkMapper(
            $pageRepository,
            $tagRepository,
            $renderer,
            $linker,
            $tagLinkMapper
        );

        $this->setDefaultMapper($pageLinkMapper);

        $this->registerEntityMapper(new NewsLinkMapper($renderer, $linker));
        $this->registerEntityMapper($tagLinkMapper);
    }
}
