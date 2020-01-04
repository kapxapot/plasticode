<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Config\ParsingConfig;
use Plasticode\Core\Renderer;
use Plasticode\Parsing\Parser;
use Slim\Views\Twig;

abstract class ParsingTestCase extends TestCase
{
    /** @var \Plasticode\Config\Interfaces\ParsingConfigInterface */
    protected $config;

    /** @var \Slim\Views\Twig */
    private $view;

    /** @var \Plasticode\Core\Interfaces\RendererInterface */
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

    protected function createParser() : Parser
    {
        return new Parser($this->config, $this->renderer);
    }
}
