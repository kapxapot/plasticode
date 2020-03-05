<?php

namespace Plasticode\Testing\Seeders;

use Plasticode\Models\Tag;
use Plasticode\Testing\Seeders\Interfaces\ArraySeederInterface;

class TagSeeder implements ArraySeederInterface
{
    /**
     * @return Tag[]
     */
    public function seed() : array
    {
        return [
            new Tag(
                [
                    'id' => 1,
                    'tag' => 'warcraft',
                    'entity_type' => 'pages',
                    'entity_id' => 2
                ]
            ),
        ];
    }
}
