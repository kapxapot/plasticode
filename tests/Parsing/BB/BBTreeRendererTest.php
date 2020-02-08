<?php

namespace Plasticode\Tests\Parsing\BB;

use Plasticode\Config\BBContainerConfig;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeRenderer;
use Plasticode\Tests\BaseRenderTestCase;

final class BBTreeRendererTest extends BaseRenderTestCase
{
    /**
     * Renders BB container tree from $text.
     *
     * @param string $text
     * @return string
     */
    private function renderTree(string $text) : string
    {
        $config = new BBContainerConfig();

        $sequencer = new BBSequencer();
        $seq = $sequencer->getSequence($text, $config->getTags());

        $treeBuilder = new BBTreeBuilder();
        $tree = $treeBuilder->build($seq);

        $treeRenderer = new BBTreeRenderer($this->renderer);
        return $treeRenderer->render($tree, $config);
    }

    /**
     * @covers BBTreeRenderer
     */
    public function testRenderQuote() : void
    {
        $text = $this->renderTree(
            '[quote|author|date]text[/quote]'
        );

        $this->assertNotEmpty($text);
    }

    public function testRenderDanglingEnd() : void
    {
        $text = $this->renderTree(
            '[/spoiler]'
        );

        $this->assertEquals('<p>[/spoiler]</p>', $text);
    }

    public function testBuildDanglingStart() : void
    {
        $text = $this->renderTree(
            '[spoiler]'
        );

        $this->assertEquals('<p>[spoiler]</p>', $text);
    }
}
