<?php

namespace Plasticode\Mapping\Providers;

use Plasticode\External\Gravatar;
use Plasticode\External\Telegram;
use Plasticode\External\Twitch;
use Plasticode\External\Twitter;
use Plasticode\Mapping\Providers\Generic\MappingProvider;
use Plasticode\Settings\Interfaces\SettingsProviderInterface;
use Psr\Container\ContainerInterface;

class ExternalProvider extends MappingProvider
{
    public function getMappings(): array
    {
        return [
            Gravatar::class => fn (ContainerInterface $c) => new Gravatar(),

            Twitch::class =>
                fn (ContainerInterface $c) => new Twitch(
                    $c->get(SettingsProviderInterface::class)->get('twitch')
                ),

            Telegram::class =>
                fn (ContainerInterface $c) => new Telegram(
                    $c->get(SettingsProviderInterface::class)->get('telegram')
                ),

            Twitter::class =>
                fn (ContainerInterface $c) => new Twitter(
                    $c->get(SettingsProviderInterface::class)->get('twitter')
                ),
        ];
    }
}
