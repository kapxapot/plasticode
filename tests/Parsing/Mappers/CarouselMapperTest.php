<?php

namespace Plasticode\Tests\Parsing\Mappers;

use PHPUnit\Framework\TestCase;
use Plasticode\Parsing\Mappers\CarouselMapper;
use Plasticode\Parsing\Parsers\BB\Nodes\TagNode;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Util\Text;
use Plasticode\ViewModels\CarouselSlide;
use Plasticode\ViewModels\CarouselViewModel;

final class CarouselMapperTest extends TestCase
{
    public function testMap() : void
    {
        $text = Text::fromLines(
            [
                'http://img.ru/1616    Some image',
                '//some/other/link',
            ]
        );

        $parsingContext = ParsingContext::fromText($text);

        $tagNode = new TagNode('carousel', [], $parsingContext->text);
        
        $mapper = new CarouselMapper();
        
        $viewContext = $mapper->map($tagNode, $parsingContext);

        /** @var CarouselViewModel */
        $model = $viewContext->model();

        $this->assertInstanceOf(CarouselViewModel::class, $model);
        $this->assertIsNumeric($model->id());
        $this->assertContainsOnlyInstancesOf(CarouselSlide::class, $model->slides());
        $this->assertCount(2, $model->slides());

        $slide1 = $model->slides()[0];

        $this->assertEquals('http://img.ru/1616', $slide1->src());
        $this->assertEquals('Some image', $slide1->caption());

        $slide2 = $model->slides()[1];

        $this->assertEquals('//some/other/link', $slide2->src());
        $this->assertNull($slide2->caption());

        $resultContext = $viewContext->parsingContext();

        $this->assertNotNull($resultContext);
        $this->assertEquals(
            ['http://img.ru/1616', '//some/other/link'],
            $resultContext->largeImages
        );
    }
}
