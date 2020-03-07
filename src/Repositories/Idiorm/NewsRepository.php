<?php

namespace Plasticode\Repositories\Idiorm;

use Plasticode\Models\News;
use Plasticode\Repositories\Idiorm\Basic\IdiormRepository;
use Plasticode\Repositories\Interfaces\NewsRepositoryInterface;

class NewsRepository extends IdiormRepository implements NewsRepositoryInterface
{
    protected $entityClass = News::class;
}
