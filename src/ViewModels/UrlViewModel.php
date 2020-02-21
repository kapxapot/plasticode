<?php

namespace Plasticode\ViewModels;

class UrlViewModel extends ViewModel
{
    /** @var string */
    private $url;

    /** @var string|null */
    private $text;

    /** @var string|null */
    private $title;

    /** @var string|null */
    private $style;

    /** @var string|null */
    private $rel;

    /** @var array|null */
    private $data;

    public function __construct(string $url, ?string $text, ?string $title = null, ?string $style = null, ?string $rel = null, ?array $data = null)
    {
        $this->url = $url;
        $this->text = $text;
        $this->title = $title;
        $this->style = $style;
        $this->rel = $rel;
        $this->data = $data;
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

    public function title() : ?string
    {
        return $this->title;
    }

    public function style() : ?string
    {
        return $this->style;
    }

    public function rel() : ?string
    {
        return $this->rel;
    }

    public function data() : ?array
    {
        return $this->data;
    }
}
