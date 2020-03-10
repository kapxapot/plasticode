<?php

namespace Plasticode\Generators;

use Plasticode\Repositories\Interfaces\MenuRepositoryInterface;
use Psr\Container\ContainerInterface;

class MenusGenerator extends EntityGenerator
{
    /** @var MenuRepositoryInterface */
    protected $menuRepository;

    public function __construct(ContainerInterface $container, string $entity)
    {
        parent::__construct($container, $entity);

        $this->menuRepository = $container->menuRepository;
    }

    public function getRules(array $data, $id = null) : array
    {
        $rules = parent::getRules($data, $id);
        
        $rules['link'] = $this->rule('url');
        $rules['text'] = $this->rule('text');
        $rules['position'] = $this->rule('posInt');
        
        return $rules;
    }

    public function afterLoad(array $item) : array
    {
        $item = parent::afterLoad($item);

        $menu = $this->menuRepository->get($item[$this->idField]);

        $item['url'] = $menu->url();

        return $item;
    }
}
