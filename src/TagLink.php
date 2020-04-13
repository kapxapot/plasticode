<?php

namespace Plasticode;

class TagLink
{
    private string $tag;
    private string $url;

    public function __construct(string $tag, string $url)
    {
        $this->tag = $tag;
        $this->url = $url;
    }

    public function text() : string
    {
        return $this->tag;
    }

    public function url() : string
    {
        return $this->url;
    }
}
