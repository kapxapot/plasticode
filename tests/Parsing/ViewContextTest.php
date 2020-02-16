<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Tests\Dummies\DummyViewModel;

final class ViewContextTest extends TestCase
{
    public function testHasParsingContext() : void
    {
        $model = new DummyViewModel();
        $parsingContext = ParsingContext::fromText('ababa');
        $viewContext = new ViewContext($model, $parsingContext);

        $this->assertTrue($viewContext->hasParsingContext());
    }

    public function testHasNoParsingContext() : void
    {
        $model = new DummyViewModel();
        $viewContext = new ViewContext($model);

        $this->assertFalse($viewContext->hasParsingContext());
    }
}
