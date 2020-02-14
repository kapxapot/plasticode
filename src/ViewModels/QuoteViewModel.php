<?php

namespace Plasticode\ViewModels;

/**
 * BB quote view model.
 * 
 * @property string $text
 * @property string|null $author
 * @prop
 */
class QuoteViewModel extends ViewModel
{
    private $text;
    private $author;
    private $url;
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
        
    }
}
