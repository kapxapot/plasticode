<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;
use Plasticode\Config\Parsing\ReplacesConfig;
use Plasticode\Tests\BaseRenderTestCase;

abstract class BaseParsingRenderTestCase extends BaseRenderTestCase
{
    /** @var ReplacesConfigInterface */
    protected $config;

    protected function setUp() : void
    {
        parent::setUp();

        $this->config = new ReplacesConfig();
    }

    protected function tearDown() : void
    {
        unset($this->config);

        parent::tearDown();
    }
}
