<?php

namespace Plasticode\ViewModels;

class UrlViewModel extends ViewModel
{
    /** @var string */
    private $url;

    /** @var string|null */
    private $text;

    public function __construct(string $url, ?string $text)
    {
        $this->url = $url;
        $this->text = $text;
    }

    public function url() : string
    {
        return $this->url;
    }

    /**
     * Returns link text, if empty = url.
     *
     * @return string
     */
    public function text() : string
    {
        return $this->text ?? $this->url;
    }
}
