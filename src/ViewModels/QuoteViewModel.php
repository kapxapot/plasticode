<?php

namespace Plasticode\ViewModels;

/**
 * BB quote view model.
 */
class QuoteViewModel extends ViewModel
{
    /** @var string */
    private $text;

    /** @var string|null */
    private $author;

    /** @var string|null */
    private $url;

    /** @var string[] */
    private $chunks;

    /**
     * @param string $text
     * @param string|null $author
     * @param string|null $url
     * @param string[] $chunks
     */
    public function __construct(string $text, ?string $author, ?string $url, array $chunks)
    {
        parent::__construct();

        $this->text = $text;
        $this->author = $author;
        $this->url = $url;
        $this->chunks = $chunks;
    }

    public function text() : string
    {
        return $this->text;
    }

    /**
     * Author name.
     *
     * @return string|null
     */
    public function author() : ?string
    {
        return $this->author;
    }

    public function url() : ?string
    {
        return $this->url;
    }

    /**
     * Other chunks.
     *
     * @return string[]
     */
    public function chunks() : array
    {
        return $this->chunks;
    }
}
