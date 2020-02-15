<?php

namespace Plasticode\Parsing;

use Plasticode\ViewModels\ViewModel;

/**
 * Aggregate for ViewModel + ParsingContext.
 */
class ViewContext
{
    /** @var ViewModel */
    private $model;

    /** @var ParsingContext|null */
    private $parsingContext;

    public function __construct(ViewModel $model, ?ParsingContext $parsingContext = null)
    {
        $this->model = $model;
        $this->parsingContext = $parsingContext;
    }

    public function model() : ViewModel
    {
        return $this->model;
    }

    public function parsingContext() : ?ParsingContext
    {
        return $this->parsingContext;
    }

    /**
     * Checks if the view context has parsing context.
     *
     * @return boolean
     */
    public function hasParsingContext() : bool
    {
        return !is_null($this->parsingContext);
    }
}
