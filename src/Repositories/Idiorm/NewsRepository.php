<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\News;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Idiorm\Traits\TagsRepository;
use Plasticode\Repositories\Interfaces\NewsRepositoryInterface;

class NewsRepository extends IdiormRepository implements NewsRepositoryInterface
{
    use TagsRepository;

    protected string $entityClass = News::class;
}
