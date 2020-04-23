<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\News;
use Plasticode\Repositories\Idiorm\Basic\TaggedRepository;
use Plasticode\Repositories\Interfaces\NewsRepositoryInterface;

class NewsRepository extends TaggedRepository implements NewsRepositoryInterface
{
    protected string $entityClass = News::class;
}
