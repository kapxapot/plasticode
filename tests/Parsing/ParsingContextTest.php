<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Collection;
use Plasticode\Parsing\ContentsItem;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Date;

final class ParsingContextTest extends TestCase
{
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
        $json = '{"text":"some text","contents":[{"level":1,"label":"1","text":"Hey"},{"level":1,"label":"2","text":"Yay"}],"largeImages":["largeImage1","largeImage2"],"images":["image1","image2"],"videos":["video1","video2"],"updatedAt":"imma date"}';

        $context = ParsingContext::fromJson($json);

        $this->assertEquals(
            [
                'some text',
                1,
                '1',
                'Hey',
                1,
                '2',
                'Yay',
                'largeImage1',
                'largeImage2',
                'image1',
                'image2',
                'video1',
                'video2',
                'imma date'
            ],
            [
                $context->text,
                $context->contents->first()->level,
                $context->contents->first()->label,
                $context->contents->first()->text,
                $context->contents->skip(1)->first()->level,
                $context->contents->skip(1)->first()->label,
                $context->contents->skip(1)->first()->text,
                $context->largeImages[0],
                $context->largeImages[1],
                $context->images[0],
                $context->images[1],
                $context->videos[0],
                $context->videos[1],
                $context->updatedAt
            ]
        );
    }
}
