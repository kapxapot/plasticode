<?php

namespace Plasticode\Models\Traits;

/**
 * @method static withUrl(string|callable|null $url)
 */
trait Linkable
{
    protected string $urlPropertyName = 'url';

    public function url() : ?string
    {
        return $this->getWithProperty(
            $this->urlPropertyName
        );
    }
}
