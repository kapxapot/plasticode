<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Core\Interfaces\RendererInterface;
use Plasticode\Tests\Factories\RendererFactory;

abstract class BaseRenderTestCase extends TestCase
{
    /** @var RendererInterface */
    protected $renderer;

    protected function setUp() : void
    {
        parent::setUp();
        
        $this->renderer = RendererFactory::make();
    }

    protected function tearDown() : void
    {
        unset($this->renderer);

        parent::tearDown();
    }
}
