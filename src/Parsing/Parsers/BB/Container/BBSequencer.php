<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\EndElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\SequenceElement;
use Plasticode\Parsing\Parsers\BB\Container\SequenceElements\StartElement;
use Plasticode\Parsing\Parsers\BB\Traits\BBAttributeParser;
use Plasticode\Util\Text;

class BBSequencer
{
    use BBAttributeParser;

    /**
     * Splits text into sequence of starting tags, ending tags and text.
     * 
     * @param string $text Text to sequence
     * @param string[] $ctags Known BB container tags
     * @return SequenceElement[]
     */
    public function getSequence(string $text, array $ctags) : array
    {
        if (empty($ctags)) {
            return [
                new SequenceElement($text)
            ];
        }
        
        $ctagsStr = implode('|', $ctags);
        
        $parts = preg_split(
            '/(\[\/?(?:' . $ctagsStr . ')[^\[]*\])/Ui', $text, -1,
            PREG_SPLIT_DELIM_CAPTURE
        );
        
        $parts = array_map(
            function ($part) {
                return Text::trimBrs($part);
            },
            $parts
        );
        
        $sequence = [];
        
        foreach ($parts as $part) {
            if (preg_match('/\[(' . $ctagsStr . ')([^\[]*)\]/Ui', $part, $matches)) {
                // bb container start
                $tag = $matches[1];
                $attrs = $this->parseAttributes($matches[2]);
                
                $sequence[] = new StartElement($tag, $attrs, $part);
            } elseif (preg_match('/\[\/(' . $ctagsStr . ')\]/Ui', $part, $matches)) {
                // bb container end
                $tag = $matches[1];

                $sequence[] = new EndElement($tag, $part);
            } elseif (strlen($part) > 0) {
                // some text
                $sequence[] = new SequenceElement($part);
            }
        }

        return $sequence;
    }
}
