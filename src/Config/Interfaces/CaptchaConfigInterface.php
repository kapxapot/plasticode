<?php

namespace Plasticode\Config\Interfaces;

interface CaptchaConfigInterface
{
    /**
     * Returns replaces for string scrambling.
     *
     * @return array<string, string[]>
     */
    public function getReplaces(): array;
}
