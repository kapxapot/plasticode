<?php

namespace Plasticode\Tests\Parsing\BB;

use PHPUnit\Framework\TestCase;
use Plasticode\Parsing\Parsers\BB\Container\BBSequencer;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\SequenceElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\StartElement;

final class BBSequencerTest extends TestCase
{
    /** @var string[] */
    private $ctags = ['quote', 'spoiler', 'list'];

    /**
     * @covers BBSequencer
     */
    public function testGetSequence() : void
    {
        $sequencer = new BBSequencer();

        $seq = $sequencer->getSequence(
            '[quote]test test[spoiler]blah[/spoiler][/quote]some text',
            $this->ctags
        );

        var_dump($seq);

        $this->assertCount(7, $seq);
        $this->assertInstanceOf(StartElement::class, $seq[0]);
        $this->assertEquals('quote', $seq[0]->tag);
        $this->assertEquals('[quote]', $seq[0]->text);
        $this->assertInstanceOf(SequenceElement::class, $seq[1]);
    }
}
