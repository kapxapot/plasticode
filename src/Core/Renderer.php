<?php

namespace Plasticode\Core;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\ViewModels\UrlViewModel;
use Slim\Views\Twig;

class Renderer implements RendererInterface
{
    /**
     * View
     *
     * @var Slim\Views\Twig
     */
    protected $view;
    
    public function __construct(Twig $view)
    {
        $this->view = $view;
    }
    
    public function text(string $text, ?string $style = null, ?string $id = null) : string
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
     *
     * @param UrlViewModel $model
     * @return string
     */
    public function url(UrlViewModel $model) : string
    {
        return $this->component('url', $model);
    }

    public function noUrl(string $text, ?string $title = null) : string
    {
        return $this->component(
            'no_url',
            [
                'text' => $text,
                'title' => $title,
            ]
        );
    }

    public function next() : string
    {
        return $this->component('next');
    }
    
    public function prev() : string
    {
        return $this->component('prev');
    }

    public function component(string $name, $data = null) : string
    {
        if ($data instanceof ArrayableInterface) {
            $data = $data->toArray();
        }

        return $this->view->fetch(
            'components/spaceless.twig',
            [
                'name' => $name,
                'data' => $data ?? [],
            ]
        );
    }
}
