<?php

namespace Plasticode\Hydrators\Generic;

use Plasticode\Core\Interfaces\LinkerInterface;
use Plasticode\Models\Generic\DbModel;
use Plasticode\Models\Generic\NewsSource;
use Plasticode\Parsing\Interfaces\ParserInterface;
use Plasticode\Parsing\Parsers\CutParser;
use Plasticode\Repositories\Interfaces\UserRepositoryInterface;

abstract class NewsSourceHydrator extends ParsingHydrator
{
    protected UserRepositoryInterface $userRepository;

    protected CutParser $cutParser;
    protected LinkerInterface $linker;

    public function __construct(
        UserRepositoryInterface $userRepository,
        CutParser $cutParser,
        LinkerInterface $linker,
        ParserInterface $parser
    )
    {
        parent::__construct($parser);

        $this->userRepository = $userRepository;

        $this->cutParser = $cutParser;
        $this->linker = $linker;
    }

    /**
     * @param NewsSource $entity
     */
    public function hydrate(DbModel $entity): NewsSource
    {
        return $entity
            ->withParsed(
                $this->frozen(
                    fn () => $this->parse($entity->rawText())
                )
            )
            ->withFullText(
                $this->frozen(
                    fn () =>
                    $this->cutParser->full(
                        $entity->parsedText()
                    )
                )
            )
            ->withShortText(
                $this->frozen(
                    fn () =>
                    $this->cutParser->short(
                        $entity->parsedText()
                    )
                )
            )
            ->withTagLinks(
                fn () => $this->linker->tagLinks($entity)
            )
            ->withCreator(
                fn () => $this->userRepository->get($entity->createdBy)
            )
            ->withUpdater(
                fn () => $this->userRepository->get($entity->updatedBy)
            );
    }
}
