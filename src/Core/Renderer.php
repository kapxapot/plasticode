<?php

namespace Plasticode\Core;

use Slim\Views\Twig;

class Renderer
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
    
    public function text(string $text, string $style = null, $id = null) : string
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

    public function next() : string
    {
        return $this->component('next');
    }
    
    public function prev() : string
    {
        return $this->component('prev');
    }

    public function component($name, $data = null) : string
    {
        return $this->view->fetch(
            'components/spaceless.twig',
            [
                'name' => $name,
                'data' => $data ?? [],
            ]
        );
    }
}
