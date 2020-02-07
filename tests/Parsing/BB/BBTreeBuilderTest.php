<?php

namespace Plasticode\Tests\Parsing\BB;

use PHPUnit\Framework\TestCase;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder;
use Plasticode\Parsing\Parsers\BB\Container\Nodes\Node;
use Plasticode\Parsing\Parsers\BB\Container\Nodes\TagNode;

final class BBTreeBuilderTest extends TestCase
{
    /** @var string[] */
    private $ctags = ['quote', 'spoiler', 'list'];

    /**
     * @covers BBTreeBuilder
     */
    public function testBuild() : void
    {
        $sequencer = new BBSequencer();

        $seq = $sequencer->getSequence(
            '[quote|some_attr|another]test [b]bold[/b] test[spoiler]blah[/spoiler][/quote]some [img|image.jpg] text',
            $this->ctags
        );

        $treeBuilder = new BBTreeBuilder();

        $tree = $treeBuilder->build($seq);

        $this->assertContainsOnlyInstancesOf(Node::class, $tree);
        $this->assertCount(2, $tree);

        /** @var TagNode */
        $quoteNode = $tree[0];

        $this->assertInstanceOf(TagNode::class, $quoteNode);
        $this->assertEquals('quote', $quoteNode->tag);
        $this->assertEquals(['some_attr', 'another'], $quoteNode->attributes);
        $this->assertEquals('[quote|some_attr|another]', $quoteNode->text);
        $this->assertCount(2, $quoteNode->children);

        /** @var Node */
        $textNode = $tree[1];

        $this->assertEquals('some [img|image.jpg] text', $textNode->text);

        /** @var Node */
        $textNode2 = $quoteNode->children[0];

        $this->assertEquals('test [b]bold[/b] test', $textNode2->text);

        /** @var TagNode */
        $spoilerNode = $quoteNode->children[1];

        $this->assertInstanceOf(TagNode::class, $spoilerNode);
        $this->assertEquals('spoiler', $spoilerNode->tag);
        $this->assertEmpty($spoilerNode->attributes);
        $this->assertEquals('[spoiler]', $spoilerNode->text);
        $this->assertCount(1, $spoilerNode->children);

        /** @var Node */
        $textNode3 = $spoilerNode->children[0];

        $this->assertEquals('blah', $textNode3->text);
    }
}
