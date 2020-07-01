<?php

namespace Plasticode\Twig\Extensions;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TranslatorExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    )
    {
        $this->translator = $translator;
    }

    public function getName() : string
    {
        return 'translator';
    }

    public function getFunctions() : array
    {
        return [
            new TwigFunction('translate', [$this, 'translate']),
        ];
    }

    public function translate(string $value) : string
    {
        return $this->translator->translate($value);
    }
}
