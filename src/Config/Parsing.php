<?php

namespace Plasticode\Config;

class Parsing
{
    public function getCleanupReplaces()
    {
        return [
            '</p><br/>' => '</p><p>',
            '(<p>)+<p' => '<p',
            '(</p>)+' => '</p>',
            '<p><div' => '<div',
            '</div></p>' => '</div>',
            '<br/><div' => '<div',
            '</div><br/>' => '</div>',
            '<p><ul>' => '<ul>',
            '</ul></p>' => '</ul>',
            '<p><figure' => '<figure',
            '</figure></p>' => '</figure>',
        ];
    }

    public function getReplaces()
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
