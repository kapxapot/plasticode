<?php

namespace Plasticode\Config;

use Plasticode\Config\Interfaces\TagsConfigInterface;

class TagsConfig implements TagsConfigInterface
{
    /**
     * @var array<string, string>
     */
    private array $map;

    public function __construct(array $map = [])
    {
        $this->map = $map;
    }

    public function getTab(string $class): ?string
    {
        return $this->map[$class] ?? null;
    }
}
