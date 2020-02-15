<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;

class ReplacesConfig implements ReplacesConfigInterface
{
    public function getCleanupReplaces() : array
    {
        return [
            '</p><br/>' => '</p><p>',
            '(<p>)+<p' => '<p',
            '(</p>)+' => '</p>',
            '<p><(div|figure)' => '<$2',
            '</(div|figure)></p>' => '</$2>',
            '<br/><div' => '<div',
            '</div><br/>' => '</div>',
            '<p><(u|o)l>' => '<$2l>',
            '</(u|o)l></p>' => '</$2l>',
        ];
    }

    public function getReplaces() : array
    {
        return [
            '[center]' => '<div class="center">',
            '[/center]' => '</div>',
            '[b]' => '<b>',
            '[/b]' => '</b>',
            '[right]' => '<div class="right">',
            '[/right]' => '</div>',
            '[i]' => '<i>',
            '[/i]' => '</i>',
            '[s]' => '<strike>',
            '[/s]' => '</strike>',
            '[u]' => '<u>',
            '[/u]' => '</u>',
            '[rightblock]' => '<div class="pull-right">',
            '[/rightblock]' => '</div>',
            '[leftblock]' => '<div class="pull-left">',
            '[/leftblock]' => '</div>',
            '[clear]' => '<div class="clearfix"></div>',
            ' -- ' => ' — ',
        ];
    }
}
