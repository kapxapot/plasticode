<?php

namespace Plasticode\Core\Interfaces;

use Plasticode\Models\News;
use Plasticode\Models\Page;

interface LinkerInterface
{
    public function abs(string $url = null) : string;
    public function page(Page $page) : string;
    public function news(News $news) : string;
    public function tag(string $tag = null, string $tab = null) : string;
    public function youtube(string $code) : string;
}
