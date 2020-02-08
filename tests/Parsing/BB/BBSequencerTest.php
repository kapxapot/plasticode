<?php

namespace Plasticode\Tests\Parsing\BB;

use PHPUnit\Framework\TestCase;
use Plasticode\Config\BBContainerConfig;
use Plasticode\Parsing\Interfaces\MapperSourceInterface;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\EndElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\SequenceElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\StartElement;

final class BBSequencerTest extends TestCase
{
    /** @var MapperSourceInterface */
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
     * @covers BBSequencer
     */
    public function testGetSequence() : void
    {
        $sequencer = new BBSequencer();

        $seq = $sequencer->getSequence(
            '[quote|some_attr|another]test [b]bold[/b] test[spoiler]blah[/spoiler][/quote]some [img|image.jpg] text',
            $this->config->getTags()
        );

        $this->assertContainsOnlyInstancesOf(SequenceElement::class, $seq);
        $this->assertCount(7, $seq);
        
        /** @var StartElement */
        $quoteStart = $seq[0];

        $this->assertInstanceOf(StartElement::class, $quoteStart);
        $this->assertEquals('quote', $quoteStart->tag);
        $this->assertEquals('[quote|some_attr|another]', $quoteStart->text);
        $this->assertEquals(['some_attr', 'another'], $quoteStart->attributes);

        /** @var SequenceElement */
        $testText = $seq[1];

        $this->assertInstanceOf(SequenceElement::class, $testText);
        $this->assertEquals('test [b]bold[/b] test', $testText->text);

        /** @var StartElement */
        $spoilerStart = $seq[2];

        $this->assertInstanceOf(StartElement::class, $spoilerStart);
        $this->assertEquals('spoiler', $spoilerStart->tag);
        $this->assertEquals('[spoiler]', $spoilerStart->text);
        $this->assertEmpty($spoilerStart->attributes);

        /** @var SequenceElement */
        $testText2 = $seq[3];

        $this->assertInstanceOf(SequenceElement::class, $testText2);
        $this->assertEquals('blah', $testText2->text);

        /** @var EndElement */
        $spoilerEnd = $seq[4];

        $this->assertInstanceOf(EndElement::class, $spoilerEnd);
        $this->assertEquals('spoiler', $spoilerEnd->tag);
        $this->assertEquals('[/spoiler]', $spoilerEnd->text);

        /** @var EndElement */
        $quoteEnd = $seq[5];

        $this->assertInstanceOf(EndElement::class, $quoteEnd);
        $this->assertEquals('quote', $quoteEnd->tag);
        $this->assertEquals('[/quote]', $quoteEnd->text);

        /** @var SequenceElement */
        $testText3 = $seq[6];

        $this->assertInstanceOf(SequenceElement::class, $testText3);
        $this->assertEquals('some [img|image.jpg] text', $testText3->text);
    }
}
