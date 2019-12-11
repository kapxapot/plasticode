<?php

namespace Plasticode\Config\Interfaces;

interface CaptchaConfigInterface
{
    /**
     * Returns replaces for messing the strings.
     *
     * @return array
     */
    public function getReplaces() : array;
}
