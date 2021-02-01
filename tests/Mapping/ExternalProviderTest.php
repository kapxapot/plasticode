<?php

namespace Plasticode\Tests\Mapping;

use Plasticode\External\Gravatar;
use Plasticode\External\Telegram;
use Plasticode\External\Twitch;
use Plasticode\External\Twitter;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;

final class ExternalProviderTest extends AbstractProviderTest
{
    protected function getOuterDependencies(): array
    {
        return [
            SettingsProviderInterface::class,
        ];
    }

    public function testWiring(): void
    {
        $this->check(Gravatar::class);
        $this->check(Telegram::class);
        $this->check(Twitch::class);
        $this->check(Twitter::class);
    }
}
