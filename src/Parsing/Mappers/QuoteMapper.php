<?php

namespace Plasticode\Parsing\Mappers;

use Plasticode\Parsing\Interfaces\MapperInterface;

class QuoteMapper implements MapperInterface
{
    public function map(string $content, array $attrs) : array
    {
        $author = null;
        $url = null;
        $chunks = [];

        foreach ($attrs as $attr) {
            if (strpos($attr, 'http') === 0) {
                $url = $attr;
            } elseif (!$author) {
                $author = $attr;
            } else {
                $chunks[] = $attr;
            }
        }
        
        return [
            'text' => $content,
            'author' => $author,
            'url' => $url,
            'chunks' => $chunks,
        ];
    }
}
