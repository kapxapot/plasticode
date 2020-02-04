<?php

namespace Plasticode\Parsing\Parsers\BB\Container;

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
     */
    public function getSequence(string $text, array $ctags) : array
    {
        if (empty($ctags)) {
            return [$text];
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
                
                $sequence[] = [
                    'type' => 'start',
                    'tag' => $tag,
                    'attributes' => $attrs,
                    'text' => $part,
                ];
            } elseif (preg_match('/\[\/(' . $ctagsStr . ')\]/Ui', $part, $matches)) {
                // bb container end
                $tag = $matches[1];

                $sequence[] = [
                    'type' => 'end',
                    'tag' => $tag,
                    'text' => $part,
                ];
            } elseif (strlen($part) > 0) {
                // some text
                $sequence[] = $part;
            }
        }

        return $sequence;
    }
}
