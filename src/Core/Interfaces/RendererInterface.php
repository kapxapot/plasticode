<?php

namespace Plasticode\Core\Interfaces;

use Plasticode\ViewModels\UrlViewModel;

interface RendererInterface
{
    /**
     * Renders text.
     *
     * @param string $text
     * @param string|null $style
     * @param string|integer|null $id
     * @return string
     */
    public function text(string $text, ?string $style = null, ?string $id = null) : string;

    /**
     * Renders component.
     *
     * @param string $name
     * @param mixed|null $data
     * @return string
     */
    public function component(string $name, $data = null) : string;

    /**
     * Renders url.
     *
     * @param UrlViewModel $model
     * @return string
     */
    public function url(UrlViewModel $model) : string;

    /**
     * Renders a placeholder instead of url (when there's no url).
     *
     * @param string $text
     * @param string|null $title
     * @return string
     */
    public function noUrl(string $text, ?string $title = null) : string;

    /**
     * Renders "prev" glyph.
     *
     * @return string
     */
    public function prev() : string;

    /**
     * Renders "next" glyph.
     *
     * @return string
     */
    public function next() : string;
}
