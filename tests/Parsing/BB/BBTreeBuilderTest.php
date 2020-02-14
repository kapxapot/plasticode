<?php

namespace Plasticode\Tests\Parsing\BB;

use PHPUnit\Framework\TestCase;
use Plasticode\Config\Parsing\BBContainerConfig;
use Plasticode\Parsing\Interfaces\TagMapperSourceInterface;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder;
use Plasticode\Parsing\Parsers\BB\Nodes\Node;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;

/**
 * @covers \Plasticode\Parsing\Parsers\BB\Container\BBTreeBuilder
 */
final class BBTreeBuilderTest extends TestCase
{
    /** @var TagMapperSourceInterface */
    private $config;

    protected function setUp() : void
    {
        parent::setUp();

        $this->config = new BBContainerConfig();
    }

    protected function tearDown() : void
    {
        unset($this->config);

        parent::tearDown();
    }

    /**
     * Builds BB Node tree from $text.
     *
     * @param string $text
     * @return Node[]
     */
    private function buildTree(string $text) : array
    {
        $sequencer = new BBSequencer();
        $seq = $sequencer->getSequence($text, $this->config->getTags());

        $treeBuilder = new BBTreeBuilder();
        return $treeBuilder->build($seq);
    }

    public function testBuild() : void
    {
        $tree = $this->buildTree(
            '[quote|some_attr|another]test [b]bold[/b] test[spoiler]blah[/spoiler][/quote]some [img]image.jpg[/img] text'
        );

        $this->assertContainsOnlyInstancesOf(Node::class, $tree);
        $this->assertCount(2, $tree);

        /** @var TagNode */
        $quoteNode = $tree[0];
        $this->assertInstanceOf(TagNode::class, $quoteNode);
        $this->assertEquals('quote', $quoteNode->tag());
        $this->assertEquals(['some_attr', 'another'], $quoteNode->attributes());
        $this->assertEquals('[quote|some_attr|another]', $quoteNode->text());
        $this->assertContainsOnlyInstancesOf(Node::class, $quoteNode->children());
        $this->assertCount(2, $quoteNode->children());

        /** @var Node */
        $textNode = $tree[1];
        $this->assertEquals('some [img]image.jpg[/img] text', $textNode->text());

        /** @var Node */
        $textNode2 = $quoteNode->children()[0];
        $this->assertEquals('test [b]bold[/b] test', $textNode2->text());

        /** @var TagNode */
        $spoilerNode = $quoteNode->children()[1];
        $this->assertInstanceOf(TagNode::class, $spoilerNode);
        $this->assertEquals('spoiler', $spoilerNode->tag());
        $this->assertEmpty($spoilerNode->attributes());
        $this->assertEquals('[spoiler]', $spoilerNode->text());
        $this->assertCount(1, $spoilerNode->children());

        /** @var Node */
        $textNode3 = $spoilerNode->children()[0];
        $this->assertEquals('blah', $textNode3->text());
    }

    public function testBuildDanglingEnds() : void
    {
        $tree = $this->buildTree(
            'test[/spoiler][/quote]other text'
        );

        $this->assertContainsOnlyInstancesOf(Node::class, $tree);
        $this->assertCount(4, $tree);

        /** @var Node */
        $node1 = $tree[0];
        $this->assertEquals('test', $node1->text());

        /** @var Node */
        $node2 = $tree[1];
        $this->assertEquals('[/spoiler]', $node2->text());

        /** @var Node */
        $node3 = $tree[2];
        $this->assertEquals('[/quote]', $node3->text());

        /** @var Node */
        $node4 = $tree[3];
        $this->assertEquals('other text', $node4->text());
    }

    public function testBuildDanglingStarts() : void
    {
        $tree = $this->buildTree(
            '[spoiler][quote]inner text[/quote]other text'
        );

        $this->assertContainsOnlyInstancesOf(Node::class, $tree);
        $this->assertCount(3, $tree);

        /** @var Node */
        $spoilerNode = $tree[0];
        $this->assertEquals('[spoiler]', $spoilerNode->text());

        /** @var TagNode */
        $quoteNode = $tree[1];
        $this->assertInstanceOf(TagNode::class, $quoteNode);
        $this->assertEquals('quote', $quoteNode->tag());
        $this->assertEmpty($quoteNode->attributes());
        $this->assertEquals('[quote]', $quoteNode->text());
        $this->assertCount(1, $quoteNode->children());

        /** @var Node */
        $textNode = $quoteNode->children()[0];
        $this->assertEquals('inner text', $textNode->text());

        /** @var Node */
        $textNode2 = $tree[2];
        $this->assertEquals('other text', $textNode2->text());
    }
}
