<?php

namespace Plasticode\Core\Interfaces;

interface RendererInterface
{
    /**
     * Renders text.
     *
     * @param string $text
     * @param string|null $style
     * @param string|integernull $id
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
}
