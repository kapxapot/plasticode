<?php

namespace Plasticode\Validation\Rules;

use Plasticode\Repositories\Interfaces\PageRepositoryInterface;
use Respect\Validation\Rules\AbstractRule;

class PageSlugAvailable extends AbstractRule
{
    private PageRepositoryInterface $pageRepository;

    private ?int $exceptId = null;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        ?int $exceptId = null
    )
    {
        $this->pageRepository = $pageRepository;

        $this->exceptId = $exceptId ?? 0;
    }

    /**
     * @param string $input
     */
    public function validate($input)
    {
        return $this
            ->pageRepository
            ->lookup($input, $this->exceptId)
            ->isEmpty();
    }
}
