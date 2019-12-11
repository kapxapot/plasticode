<?php

namespace Plasticode\Core\Interfaces;

interface RendererInterface
{
    public function text(string $text, string $style = null, $id = null) : string;
    public function component(string $name, ?array $data = null) : string;
}
