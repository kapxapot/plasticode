<?php

namespace Plasticode\Tests\Factories;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Renderer;
use Slim\Views\Twig;

class RendererFactory
{
    public static function make() : RendererInterface
    {
        $view = new Twig('views/bootstrap3/', ['debug' => true]);
        return new Renderer($view);
    }
}
