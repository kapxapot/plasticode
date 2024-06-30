<?php

namespace Plasticode\Auth;

use Plasticode\Auth\Interfaces\CaptchaInterface;
use Plasticode\Config\Interfaces\CaptchaConfigInterface;
use Plasticode\Core\Interfaces\SessionInterface;
use Plasticode\Util\Date;
use Plasticode\Util\Numbers;

class Captcha implements CaptchaInterface
{
    /**
     * Default time to live in minutes
     *
     * @var integer
     */
    private const DefaultTtl = 10;

    /** @var SessionInterface */
    private $session;

    /**
     * Time to live in minutes
     *
     * @var integer
     */
    private $ttl;
    
    /**
     * Init this array in constructor, otherwise your captcha will not be scrambled.
     *
     * @var array<string, string[]>
     */
    private $replacements = [];

    /**
     * @param CaptchaConfigInterface|null $config Your custom replacement config. You should provide it.
     * @param integer|null $ttl Time to live in minutes, in case of `null` the default will be used.
     */
    public function __construct(
        SessionInterface $session,
        CaptchaConfigInterface $config = null,
        int $ttl = null
    )
    {
        $this->session = $session;
        $this->ttl = $ttl ?? self::DefaultTtl;

        if ($config) {
            $this->replacements = $config->getReplaces();
        }
    }

    /**
     * Scrambles the string using replacements.
     */
    private function scramble(string $str): string
    {
        foreach ($this->replacements as $key => $reps) {
            $rep = $reps[mt_rand(0, count($reps) - 1)];
            $str = str_replace($key, $rep, $str);
        }

        return $str;
    }

    /**
     * Generates captcha.
     */
    public function generate(int $length, bool $save = false): array
    {
        $num = Numbers::generate($length);
        $string = Numbers::toString($num);

        $scrambledString = implode(
            '',
            array_map(
                fn ($v) => $this->scramble($v),
                explode(' ', $string)
            )
        );

        $result = [
            'number' => $num,
            'string' => $string,
            'captcha' => $scrambledString,
        ];

        if ($save) {
            $this->save($result);
        }

        return $result;
    }
    
    /**
     * Saves captcha to the session.
     */
    private function save(array $captcha)
    {
        $captcha['expires_at'] = Date::generateExpirationTime($this->ttl);

        $this->session->set('captcha', $captcha);
    }

    /**
     * Loads the captcha from the session and destroys it in the session
     * so the captcha can be validated only once.
     */
    private function load(): array
    {
        return $this->session->getAndDelete('captcha');
    }

    /**
     * Validates the provided number against previously generated captcha.
     *
     * @param mixed $number
     */
    public function validate($number): bool
    {
        $captcha = $this->load();

        return $captcha
            && is_numeric($number)
            && $captcha['number'] == $number
            && strtotime($captcha['expires_at']) >= time();
    }
}
