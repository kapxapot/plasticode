<?php

namespace Plasticode\Generators;

use Plasticode\Generators\Core\GeneratorContext;
use Plasticode\Generators\Generic\ChangingEntityGenerator;
use Plasticode\Models\Menu;
use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;

class MenuGenerator extends ChangingEntityGenerator
{
    private MenuRepositoryInterface $menuRepository;

    public function __construct(
        GeneratorContext $context,
        MenuRepositoryInterface $menuRepository
    )
    {
        parent::__construct($context);

        $this->menuRepository = $menuRepository;
    }

    protected function entityClass(): string
    {
        return Menu::class;
    }

    protected function getRepository(): MenuRepositoryInterface
    {
        return $this->menuRepository;
    }

    public function getRules(array $data, $id = null): array
    {
        $rules = parent::getRules($data, $id);

        $rules['link'] = $this->rule('url');
        $rules['text'] = $this->rule('text');
        $rules['position'] = $this->rule('posInt');

        return $rules;
    }

    public function afterLoad(array $item): array
    {
        $item = parent::afterLoad($item);

        $id = $item[$this->idField()];

        $menu = $this->menuRepository->get($id);

        $item['url'] = $menu->url();

        return $item;
    }
}
