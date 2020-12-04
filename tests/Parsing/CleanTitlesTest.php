<?php

namespace Plasticode\Tests\Parsing\Steps;

use Plasticode\Config\Parsing\ReplacesConfig;
use Plasticode\Parsing\Interfaces\ParsingStepInterface;
use Plasticode\Parsing\Parsers\CleanupParser;
use Plasticode\Parsing\Parsers\CompositeParser;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\Steps\NewLinesToBrsStep;
use Plasticode\Parsing\Steps\TitlesStep;
use Plasticode\Tests\BaseRenderTestCase;

final class CleanTitlesTest extends BaseRenderTestCase
{
    private ParsingStepInterface $parser;

    protected function setUp() : void
    {
        parent::setUp();

        $this->parser = new CompositeParser(
            new TitlesStep($this->renderer),
            new NewLinesToBrsStep(),
            new CleanupParser(
                new ReplacesConfig()
            )
        );
    }

    protected function tearDown() : void
    {
        unset($this->parser);

        parent::tearDown();
    }

    public function testGluedTitles() : void
    {
        $lines = [
            'sdfdsfsfd',
            '## dsfsfd',
            'dsdds',
            '### dsddsf',
            'dfsfsfd',
            '#### d sf sdf',
            'sfddsfsfd',
            '##### sdfs fsfd',
            'sfdsfsfdf',
            '###### sdf sfd sfd',
            'sdfssfsf',
            '####### s dfs fd',
            'sdf sfd dsfds'
        ];

        $context = ParsingContext::fromLines($lines);
        $parsedContext = $this->parser->parseContext($context);

        $this->assertEquals(
            '<p>sdfdsfsfd</p><h2 id="1">dsfsfd</h2><p>dsdds</p><h3 id="1_1">dsddsf</h3><p>dfsfsfd</p><h4 id="1_1_1">d sf sdf</h4><p>sfddsfsfd</p><h5 id="1_1_1_1">sdfs fsfd</h5><p>sfdsfsfdf</p><h6 id="1_1_1_1_1">sdf sfd sfd</h6><p>sdfssfsf<br/>####### s dfs fd<br/>sdf sfd dsfds</p>',
            $parsedContext->text
        );
    }

    public function testSparseTitles() : void
    {
        $lines = [
            'sdfdsfsfd',
            '',
            '## dsfsfd',
            '',
            'dsdds',
            '',
            '### dsddsf',
            '',
            'dfsfsfd',
            '',
            '#### d sf sdf',
            '',
            'sfddsfsfd',
            '',
            '##### sdfs fsfd',
            '',
            'sfdsfsfdf',
            '',
            '###### sdf sfd sfd',
            '',
            'sdfssfsf',
            '',
            '####### s dfs fd',
            '',
            'sdf sfd dsfds'
        ];

        $context = ParsingContext::fromLines($lines);
        $parsedContext = $this->parser->parseContext($context);

        $this->assertEquals(
            '<p>sdfdsfsfd</p><h2 id="1">dsfsfd</h2><p>dsdds</p><h3 id="1_1">dsddsf</h3><p>dfsfsfd</p><h4 id="1_1_1">d sf sdf</h4><p>sfddsfsfd</p><h5 id="1_1_1_1">sdfs fsfd</h5><p>sfdsfsfdf</p><h6 id="1_1_1_1_1">sdf sfd sfd</h6><p>sdfssfsf</p><p>####### s dfs fd</p><p>sdf sfd dsfds</p>',
            $parsedContext->text
        );
    }
}
