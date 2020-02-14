<?php

namespace Plasticode\Parsing;

use Plasticode\ViewModels\ViewModel;

class ViewContext
{
    private $model;
    private $context;

    public function __construct(ViewModel $model, ?ParsingContext $context = null)
    {
        $this->model = $model;
        $this->context = $context;
    }

    /**
     * View model.
     *
     * @return ViewModel
     */
    public function model() : ViewModel
    {
        return $this->model;
    }

    /**
     * Parsing context.
     *
     * @return ParsingContext
     */
    public function context() : ParsingContext
    {
        return $this->context;
    }

    /**
     * Checks if the view context has parsing context.
     *
     * @return boolean
     */
    public function hasParsingContext() : bool
    {
        return !is_null($this->context);
    }
}
