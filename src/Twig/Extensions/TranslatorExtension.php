<?php

namespace Plasticode\Twig\Extensions;

use Plasticode\Core\Interfaces\TranslatorInterface;
use Plasticode\Util\Classes;
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

    public function getName(): string
    {
        return 'translator';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('translate', [$this, 'translate']),
        ];
    }

    /**
     * @param string|object $prefix
     */
    public function translate(string $value, $prefix = null): string
    {
        if (is_object($prefix)) {
            $prefix = Classes::shortName(get_class($prefix));
        }

        if ($prefix !== null) {
            $value = sprintf('%s:%s', $prefix, $value);
        }

        return $this->translator->translate($value);
    }
}
