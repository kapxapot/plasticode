<?php

namespace Plasticode\Testing\Factories;

use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Renderer;
use Plasticode\Twig\TwigView;
use Slim\Views\Twig;

class RendererFactory
{
    public static function make() : RendererInterface
    {
        $twig = new Twig('views/bootstrap3/', ['debug' => true]);
        $view = new TwigView($twig);
        
        return new Renderer($view);
    }
}
