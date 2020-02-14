<?php

namespace Plasticode\ViewModels;

class LinkViewModel extends ViewModel
{
    /** @var string */
    private $url;

    /** @var string|null */
    private $content;

    public function __construct(string $url, ?string $content)
    {
        $this->url = $url;
        $this->content = $content;
    }

    public function url() : string
    {
        return $this->url;
    }

    /**
     * Returns link content, if empty = url.
     *
     * @return string
     */
    public function content() : string
    {
        return $this->content ?? $this->url;
    }
}
