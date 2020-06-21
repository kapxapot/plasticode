<?php

namespace Plasticode\Tests\Parsing;

use PHPUnit\Framework\TestCase;
use Plasticode\Parsing\ParsingContext;
use Plasticode\Parsing\ViewContext;
use Plasticode\Testing\Dummies\ViewModelDummy;

final class ViewContextTest extends TestCase
{
    public function testHasParsingContext() : void
    {
        $model = new ViewModelDummy();
        $parsingContext = ParsingContext::fromText('ababa');
        $viewContext = new ViewContext($model, $parsingContext);

        $this->assertTrue($viewContext->hasParsingContext());
    }

    public function testHasNoParsingContext() : void
    {
        $model = new ViewModelDummy();
        $viewContext = new ViewContext($model);

        $this->assertFalse($viewContext->hasParsingContext());
    }
}
