<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Renderer;
use Slim\Views\Twig;

abstract class BaseRenderTestCase extends TestCase
{
    /** @var Twig */
    private $view;

    /** @var RendererInterface */
    protected $renderer;

    protected function setUp() : void
    {
        parent::setUp();
        
        $this->view = new Twig('views/bootstrap3/', ['debug' => true]);
        $this->renderer = new Renderer($this->view);
    }

    protected function tearDown() : void
    {
        unset($this->renderer);
        unset($this->view);

        parent::tearDown();
    }
}
