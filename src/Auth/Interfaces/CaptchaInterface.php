<?php

namespace Plasticode\Auth\Interfaces;

interface CaptchaInterface
{
    /**
     * Generates captcha.
     *
     * @param integer $length
     * @param boolean $save
     * @return array
     */
    public function generate(int $length, bool $save = false) : array;
    
    /**
     * Validates the provided number against previously generated captcha.
     *
     * @param string|null $number
     * @return boolean
     */
    public function validate(?string $number) : bool;
}
