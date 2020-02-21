<?php

namespace Plasticode\Core;

use Plasticode\Util\Strings;

class Pagination
{
    protected $linker;
    protected $renderer;
    
    public function __construct(Linker $linker, Renderer $renderer)
    {
        $this->linker = $linker;
        $this->renderer = $renderer;
    }

    public function complex(string $url, int $count, int $index, int $pageSize) : array
    {
        $paging = [];
        $pages = [];
        
        $stepping = 1;
        $neighbours = 7;
        
        if ($count > $pageSize) {
            // prev page
            if ($index > 1) {
                $prev = $this->page(
                    $url,
                    $index - 1,
                    false,
                    $this->renderer->prev(),
                    'Предыдущая страница'
                );

                $pages[] = $prev;
                $paging['prev'] = $prev;
            }

            $pageCount = ceil($count / $pageSize);
            
            $shownIndex = 1;
            $step = ceil($pageCount / $stepping);

            while ($shownIndex <= $pageCount) {
                if ($shownIndex == 1
                    || $shownIndex >= $pageCount
                    || ($shownIndex % $step == 0)
                    || (abs($shownIndex - $index) <= $neighbours)
                ) {
                    $pages[] = $this->page($url, $shownIndex, $shownIndex == $index);
                }
                
                $shownIndex++;
            }

            // next page
            if ($index < $pageCount) {
                $next = $this->page(
                    $url,
                    $index + 1,
                    false,
                    $this->renderer->next(),
                    'Следующая страница'
                );

                $pages[] = $next;
                $paging['next'] = $next;
            }
            
            $paging['pages'] = $pages;
        }
        
        return $paging;
    }

    public function simple(string $baseUrl, int $totalPages, int $page) : array
    {
        $paging = [];

        if ($totalPages > 1) {
            $pages = [];
            
            if ($page > 1) {
                $prev = $this->page(
                    $baseUrl,
                    $page - 1,
                    false,
                    $this->renderer->prev(),
                    'Предыдущая страница'
                );

                $paging['prev'] = $prev;
                $pages[] = $prev;
            }

            for ($i = 1; $i <= $totalPages; $i++) {
                $pages[] = $this->page($baseUrl , $i, $i == $page);
            }
            
            if ($page < $totalPages) {
                $next = $this->page(
                    $baseUrl,
                    $page + 1,
                    false,
                    $this->renderer->next(),
                    'Следующая страница'
                );

                $paging['next'] = $next;
                $pages[] = $next;
            }
            
            $paging['page'] = $page;
            $paging['pages'] = $pages;
        }

        return $paging;
    }

    private function page(string $baseUrl, int $page, int $current, string $label = null, string $title = null) : array
    {
        return [
            'page' => $page,
            'current' => $current,
            'url' => $this->pageLink($baseUrl, $page),
            'label' => ($label != null) ? $label : $page,
            'title' => ($title != null) ? $title : "Страница {$page}",
        ];
    }
    
    private function pageLink(string $base, int $page) : string
    {
        if ($page <= 1) {
            return $base;
        }

        return Strings::appendQueryParam($base, 'page', $page);
    }

}
