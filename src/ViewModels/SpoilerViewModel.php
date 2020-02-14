<?php

namespace Plasticode\ViewModels;

/**
 * BB spoiler view model.
 */
class SpoilerViewModel extends ViewModel
{
    /** @var string */
    private $id;

    /** @var string */
    private $body;

    /** @var string|null */
    private $title;

    /**
     * @param string $id
     * @param string $body
     * @param string|null $title
     */
    public function __construct(string $id, string $body, ?string $title)
    {
        parent::__construct();

        $this->id = $id;
        $this->body = $body;
        $this->title = $title;
    }

    /**
     * Id that needs to be unique for the page.
     *
     * @return string
     */
    public function id() : string
    {
        return $this->id;
    }

    /**
     * Body text.
     *
     * @return string
     */
    public function body() : string
    {
        return $this->body;
    }

    /**
     * Optional spoiler title.
     *
     * @return string|null
     */
    public function title() : ?string
    {
        return $this->title;
    }
}
