<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Collection;
use Plasticode\Parsing\ContentsItem;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Date;

final class ParsingContextTest extends TestCase
{
    private $json = '{"text":"some text","contents":[{"level":1,"label":"1","text":"Hey"},{"level":1,"label":"2","text":"Yay"}],"largeImages":["largeImage1","largeImage2"],"images":["image1","image2"],"videos":["video1","video2"],"updatedAt":"imma date"}';

    public function testJsonEncode() : void
    {
        $context = ParsingContext::fromText('some text');
        
        $context->contents = Collection::make(
            [
                new ContentsItem(1, '1', 'Hey'),
                new ContentsItem(1, '2', 'Yay')
            ]
        );

        $context->largeImages = ['largeImage1', 'largeImage2'];
        $context->images = ['image1', 'image2'];
        $context->videos = ['video1', 'video2'];

        $date = Date::dbNow();

        $context->updatedAt = $date;

        $actual = json_encode($context);

        $expected = '{"text":"some text","contents":[{"level":1,"label":"1","text":"Hey"},{"level":1,"label":"2","text":"Yay"}],"largeImages":["largeImage1","largeImage2"],"images":["image1","image2"],"videos":["video1","video2"],"updatedAt":"' . $date . '"}';

        $this->assertEquals($expected, $actual);
    }

    public function testFromJson() : void
    {
        $context = ParsingContext::fromJson($this->json);

        $this->assertEquals('some text', $context->text);

        $this->assertCount(2, $context->contents);

        $contents1 = $context->contents[0];
        $contents2 = $context->contents[1];

        $this->assertEquals(
            [1, '1', 'Hey'],
            [
                $contents1->level,
                $contents1->label,
                $contents1->text
            ]
        );

        $this->assertEquals(
            [1, '2', 'Yay'],
            [
                $contents2->level,
                $contents2->label,
                $contents2->text
            ]
        );

        $this->assertEquals(['largeImage1', 'largeImage2'], $context->largeImages);
        $this->assertEquals('largeImage1', $context->largeImage());

        $this->assertEquals(['image1', 'image2'], $context->images);
        $this->assertEquals('image1', $context->image());

        $this->assertEquals(['video1', 'video2'], $context->videos);
        $this->assertEquals('video1', $context->video());

        $this->assertEquals('imma date', $context->updatedAt);
    }

    public function testClone() : void
    {
        $original = ParsingContext::fromJson($this->json);

        $context = clone $original;

        $this->assertEquals(json_encode($context), $this->json);
    }
}
