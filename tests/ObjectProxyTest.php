<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Auth\Access;
use Plasticode\Auth\Auth;
use Plasticode\Core\Cache;
use Plasticode\Core\Session;
use Plasticode\Core\SettingsProvider;
use Plasticode\Data\Db;
use Plasticode\Hydrators\TagHydrator;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;
use Plasticode\Repositories\Idiorm\TagRepository;
use Plasticode\Testing\Dummies\DummyModel;
use Plasticode\Testing\Mocks\LinkerMock;

final class ObjectProxyTest extends TestCase
{
    public function testDummyProxy() : void
    {
        $proxy = new ObjectProxy(
            fn () => new DummyModel(1, 'model')
        );

        $this->assertEquals('model', $proxy->getName());
    }

    public function testHydratorProxy() : void
    {
        $session = new Session('test');
        $auth = new Auth($session);
        $cache = new Cache();
        $settingsProvider = new SettingsProvider([]);

        $tagRepository = new TagRepository(
            new RepositoryContext(
                new Access($cache, []),
                $auth,
                $cache,
                new Db($cache, $settingsProvider)
            ),
            new ObjectProxy(
                fn () =>
                new TagHydrator(
                    new LinkerMock()
                )
            )
        );

        $this->assertNotNull($tagRepository);
    }
}