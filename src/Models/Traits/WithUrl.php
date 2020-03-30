<?php

namespace Plasticode\Models\Traits;

use Webmozart\Assert\Assert;

trait WithUrl
{
    protected ?string $url = null;

    private bool $urlInitialized = false;

    public function withUrl(?string $url) : self
    {
        $this->url = $url;
        $this->urlInitialized = true;

        return $this;
    }

    public function url() : ?string
    {
        Assert::true($this->urlInitialized);

        return $this->url;
    }
}
