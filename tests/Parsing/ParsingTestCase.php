<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Config\ParsingConfig;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Core\Renderer;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\CompositeParser;
use Plasticode\Parsing\ParsingContext;
use Slim\Views\Twig;

abstract class ParsingTestCase extends TestCase
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

    protected function createParser() : CompositeParser
    {
        return new CompositeParser($this->config, $this->renderer);
    }

    protected function parse(string $text) : ParsingContext
    {
        return $this->parseLines([$text]);
    }

    protected function parseLines(array $lines) : ParsingContext
    {
        $context = ParsingContext::fromLines($lines);
        $context = $this->step()->parse($context);

        return $context;
    }

    protected abstract function step() : ParsingStepInterface;

    protected function assertContextIsImmutable() : void
    {
        $context = ParsingContext::fromText('');
        $newContext = $this->step()->parse($context);

        $this->assertNotSame($context, $newContext);
    }
}
