<?php

namespace Plasticode\Config\Parsing;

use Plasticode\Config\Parsing\Interfaces\ReplacesConfigInterface;

class ReplacesConfig implements ReplacesConfigInterface
{
    public function getCleanupReplaces() : array
    {
        $br = '\<br\/\>';
        $pOpen = '\<p\>';
        $pClose = '\<\/p\>';
        $hOpen = '\<(h[2-6])';
        $hClose = '\<\/(h[2-6])\>';

        return [
            $pClose . $br => '</p><p>',
            '(' . $pOpen . ')+\<p' => '<p',
            '(' . $pClose . ')+' => '</p>',
            $pOpen . '\<(div|figure)' => '<$2',
            '\<\/(div|figure)\>' . $pClose => '</$2>',
            $br . '\<div' => '<div',
            '\<\/div\>' . $br => '</div>',
            $pOpen . '\<(ul|ol)\>' => '<$2>',
            '\<\/(ul|ol)\>' . $pClose => '</$2>',
            $br . $hOpen => '</p><$2',
            $hClose . $br => '</$2><p>',
            $pOpen . $hOpen => '<$2',
            $hClose . $pClose => '</$2>',
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
