<?php

namespace Plasticode\Repositories\Interfaces\Generic;

use Plasticode\Collections\Generic\NewsSourceCollection;
use Plasticode\Models\Interfaces\NewsSourceInterface;

interface NewsSourceRepositoryInterface extends ChangingRepositoryInterface, ProtectedRepositoryInterface, TaggedRepositoryInterface
{
    function get(?int $id): ?NewsSourceInterface;
    function getProtected(?int $id): ?NewsSourceInterface;
    function getAllByTag(string $tag, int $limit = 0): NewsSourceCollection;
    function getNewsByTag(string $tag, int $limit = 0): NewsSourceCollection;
    function getLatestNews(int $limit = 0, int $exceptId = 0): NewsSourceCollection;
    function getNewsCount(): int;
    function getNewsBefore(string $date, int $limit = 0): NewsSourceCollection;
    function getNewsAfter(string $date, int $limit = 0): NewsSourceCollection;
    function getNewsByYear(int $year): NewsSourceCollection;
    function getNews(?int $id): ?NewsSourceInterface;
}
