<?php

namespace Plasticode\Models\Traits;

use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\ParsingContext;

trait Description
{
    protected static function getDescriptionField() : string
    {
        return 'description';
    }

    public function parsedDescription(ParserInterface $parser) : ?ParsingContext
    {
        $descriptionField = static::getDescriptionField();
        $description = $this->{$descriptionField};

        if (strlen($description) == 0) {
            return null;
        }

        $context = $parser->parse($description);
        $context = $parser->renderLinks($context);

        return $context;
    }
}
