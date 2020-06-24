<?php

namespace Plasticode\Repositories\Interfaces\Basic;

use Plasticode\Collections\NewsSourceCollection;
use Plasticode\Models\Interfaces\NewsSourceInterface;

interface NewsSourceRepositoryInterface extends TaggedRepositoryInterface
{
    function getAllByTag(string $tag, int $limit = 0) : NewsSourceCollection;
    function getNewsByTag(string $tag, int $limit = 0) : NewsSourceCollection;
    function getLatestNews(int $limit = 0, int $exceptId = 0) : NewsSourceCollection;
    function getNewsCount() : int;
    function getNewsBefore(string $date, int $limit = 0) : NewsSourceCollection;
    function getNewsAfter(string $date, int $limit = 0) : NewsSourceCollection;
    function getNewsByYear(int $year) : NewsSourceCollection;
    function getNews(?int $id) : ?NewsSourceInterface;
}
