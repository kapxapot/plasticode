<?php

namespace Plasticode\Tests\Mapping;

use PHPUnit\Framework\TestCase;
use Plasticode\DI\Autowirer;
use Plasticode\DI\Containers\AutowiringContainer;
use Plasticode\Mapping\Aggregators\WritableMappingAggregator;
use Plasticode\Mapping\Interfaces\MappingProviderInterface;
use Prophecy\PhpUnit\ProphecyTrait;

abstract class AbstractProviderTest extends TestCase
{
    use ProphecyTrait;

    protected AutowiringContainer $container;

    public function setUp(): void
    {
        parent::setUp();

        $this->container = new AutowiringContainer(
            new Autowirer()
        );

        $dependencies = $this->getOuterDependencies();

        array_walk(
            $dependencies,
            fn (string $c) =>
                $this->container[$c] = fn () => $this->prophesize($c)->reveal()
        );

        $bootstrap = new WritableMappingAggregator($this->container);

        $bootstrap->register(
            $this->getProvider()
        );

        $bootstrap->boot();
    }

    public function tearDown(): void
    {
        unset($this->container);

        parent::tearDown();
    }

    /**
     * @return string[]
     */
    protected function getOuterDependencies(): array
    {
        return [];
    }

    abstract protected function getProvider(): MappingProviderInterface;

    abstract public function testWiring(): void;

    protected function check(string $from, ?string $to = null): void
    {
        $this->assertInstanceOf($to ?? $from, $this->container->get($from));
    }
}
