<?php

namespace Plasticode\Tests;

use PHPUnit\Framework\TestCase;
use Plasticode\Auth\Access;
use Plasticode\Auth\Auth;
use Plasticode\Core\Cache;
use Plasticode\Core\Session;
use Plasticode\Core\SettingsProvider;
use Plasticode\Data\DbMetadata;
use Plasticode\Hydrators\TagHydrator;
use Plasticode\ObjectProxy;
use Plasticode\Repositories\Idiorm\Basic\RepositoryContext;
use Plasticode\Repositories\Idiorm\TagRepository;
use Plasticode\Testing\Dummies\ModelDummy;
use Plasticode\Testing\Mocks\LinkerMock;

final class ObjectProxyTest extends TestCase
{
    public function testDummyProxy() : void
    {
        $proxy = new ObjectProxy(
            fn () => new ModelDummy(1, 'model')
        );

        $this->assertEquals('model', $proxy->getName());
        $this->assertInstanceOf(ModelDummy::class, $proxy());
    }

    public function testHydratorProxy() : void
    {
        $session = new Session('test');
        $auth = new Auth($session);
        $cache = new Cache();
        $settingsProvider = new SettingsProvider();

        $tagRepository = new TagRepository(
            new RepositoryContext(
                new Access(),
                $auth,
                $cache,
                new DbMetadata($settingsProvider)
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
