<?php

namespace Plasticode\Tests\Parsing;

use Plasticode\Config\Interfaces\ParsingConfigInterface;
use Plasticode\Config\ParsingConfig;
use Plasticode\Tests\BaseRenderTestCase;

abstract class BaseParsingRenderTestCase extends BaseRenderTestCase
{
    /** @var ParsingConfigInterface */
    protected $config;

    protected function setUp() : void
    {
        parent::setUp();

        $this->config = new ParsingConfig();
    }

    protected function tearDown() : void
    {
        unset($this->config);

        parent::tearDown();
    }
}
