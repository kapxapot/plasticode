<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Config\ParsingConfig;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Renderer;
use Slim\Views\Twig;

abstract class BaseRenderTestCase extends TestCase
{
    /** @var ParsingConfigInterface */
    protected $config;

    /** @var Twig */
    private $view;

    /** @var RendererInterface */
    protected $renderer;

    protected function setUp() : void
    {
        parent::setUp();

        $this->config = new ParsingConfig();
        
        $this->view = new Twig('views/bootstrap3/', ['debug' => true]);
        $this->renderer = new Renderer($this->view);
    }

    protected function tearDown() : void
    {
        unset($this->renderer);
        unset($this->view);
        unset($this->config);

        parent::tearDown();
    }
}
