<?php

namespace Plasticode\Auth;

use Plasticode\Core\Session;
use Plasticode\Util\Date;
use Plasticode\Util\Numbers;

class Captcha
{
    /**
     * Session
     *
     * @var Plasticode\Core\Session
     */
    private $session;

    /**
     * Time to live in minutes
     *
     * @var int
     */
    private $ttl;
    
    /**
     * Replacements
     * 
     * Init this array in constructor
     * otherwise your captcha will be not captcha
     * 
     * @var array
     */
    private $replacements = [];

    /**
     * Creates Captcha instance
     * 
     * @param Plasticode\Core\Session
     * @param array $replacements Your custom replacement rules. You MUST provide them
     * @param int $ttl Time to live in minutes
     */
    public function __construct(Session $session, array $replacements = [], int $ttl = 10)
    {
        $this->session = $session;
        $this->ttl = $ttl;
        $this->replacements = $replacements;
    }

    /**
     * Fuck up the string using replacements
     *
     * @param string $str
     * @return string
     */
    private function fuckUp(string $str) : string
    {
        foreach ($this->replacements as $key => $reps) {
            $rep = $reps[mt_rand(0, count($reps) - 1)];
            $str = str_replace($key, $rep, $str);
        }

        return $str;
    }

    /**
     * Generate captcha
     *
     * @param integer $length
     * @param boolean $save
     * @return array
     */
    public function generate(int $length, bool $save = false) : array
    {
        $num = Numbers::generate($length);
        $string = Numbers::toString($num);
        
        $fuckedUpString = implode(
            '',
            array_map(
                function ($value) {
                    return $this->fuckUp($value);
                },
                explode(' ', $string)
            )
        );

        $result = [
            'number' => $num,
            'string' => $string,
            'captcha' => $fuckedUpString,
        ];
        
        if ($save) {
            $this->save($result);
        }
        
        return $result;
    }
    
    /**
     * Save captcha to session
     *
     * @param array $captcha
     * @return void
     */
    private function save(array $captcha)
    {
        $captcha['expires_at'] = Date::generateExpirationTime($this->ttl);

        $this->session->set('captcha', $captcha);
    }

    /**
     * Load captcha from session and destroy it in session
     * so the captcha can be validated only once
     *
     * @return array
     */
    private function load() : array
    {
        return $this->session->getAndDelete('captcha');
    }
    
    /**
     * Validate the provided number against previously generated captcha
     *
     * @param mixed $number
     * @return boolean
     */
    public function validate($number) : bool
    {
        $captcha = $this->load();

        return $captcha
            && is_numeric($number)
            && $captcha['number'] == $number
            && strtotime($captcha['expires_at']) >= time();
    }
}
