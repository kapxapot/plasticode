<?php

namespace Plasticode\Testing\Seeders;

use Plasticode\Testing\Dummies\PageDummy;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;
use Plasticode\Util\Date;

class PageSeeder implements ArraySeederInterface
{
    /**
     * @return PageDummy[]
     */
    public function seed() : array
    {
        return [
            new PageDummy(
                [
                    'id' => 1,
                    'slug' => 'about-us',
                    'title' => 'About us',
                    'text' => 'We are awesome. Work with us.',
                    'published' => 1,
                    'published_at' => Date::dbNow(),
                ]
            ),
            new PageDummy(
                [
                    'id' => 2,
                    'slug' => 'illidan-stormrage',
                    'title' => 'Illidan Stormrage',
                    'text' => 'Illidan is a bad boy. Once a night elf, now a demon. Booo.',
                    'published' => 0,
                    'published_at' => null,
                ]
            ),
        ];
    }
}
