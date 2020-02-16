<?php

namespace Plasticode\Tests\Parsing\BB;

use Plasticode\Config\Parsing\BBContainerConfig;
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

    public function testRender() : void
    {
        $text = $this->renderTree(
            '[list][*]one[*]two[*]three[/list]'
        );

        $this->assertEquals(
            '<ul><li>one</li><li>two</li><li>three</p></li></ul>',
            $text
        );
    }
}
