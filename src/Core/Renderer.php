<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Interfaces\ViewInterface;
use Plasticode\Util\Arrays;
use Plasticode\ViewModels\UrlViewModel;

class Renderer implements RendererInterface
{
    protected ViewInterface $view;

    public function __construct(ViewInterface $view)
    {
        $this->view = $view;
    }

    public function text(string $text, ?string $style = null, ?string $id = null): string
    {
        return $this->component(
            'text',
            [
                'text' => $text,
                'style' => $style,
                'id' => $id,
            ]
        );
    }

    /**
     * Renders url.
     */
    public function url(UrlViewModel $model): string
    {
        return $this->component('url', $model);
    }

    public function entityUrl(string $url, string $text, ?string $title = null): string
    {
        return $this->component(
            'entity_url',
            [
                'url' => $url,
                'text' => $text,
                'title' => $title,
            ]
        );
    }

    public function noUrl(string $text, ?string $title = null): string
    {
        return $this->component(
            'no_url',
            [
                'text' => $text,
                'title' => $title,
            ]
        );
    }

    public function next(): string
    {
        return $this->component('next');
    }
    
    public function prev(): string
    {
        return $this->component('prev');
    }

    public function component(string $name, $data = null): string
    {
        return $this->view->fetch(
            'components/spaceless.twig',
            [
                'name' => $name,
                'data' => Arrays::adopt($data) ?? [],
            ]
        );
    }
}
