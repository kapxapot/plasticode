<?php

namespace Plasticode\Tests\Semantics;

use PHPUnit\Framework\TestCase;
use Plasticode\Collections\Generic\Collection;
use Plasticode\Interfaces\ArrayableInterface;
use Plasticode\Semantics\Sentence;

final class SentenceTest extends TestCase
{
    /**
     * @param array|ArrayableInterface $original
     * 
     * @dataProvider joinProvider
     */
    public function testJoin($original, string $expected): void
    {
        $this->assertEquals(
            $expected,
            Sentence::join($original)
        );
    }

    public function joinProvider(): array
    {
        return [
            [[], ''],
            [['a'], 'a'],
            [['a', 'b'], 'a, b'],
            [['a', 'b', 'c'], 'a, b, c'],
            [Collection::collect(1, 2, 3, 4), '1, 2, 3, 4'],
        ];
    }

    public function testJoinAlternativeDelimiters(): void
    {
        $this->assertEquals(
            'a.b.c',
            Sentence::join(['a', 'b', 'c'], '.')
        );
    }

    /**
     * @param array|ArrayableInterface $original
     * 
     * @dataProvider homogeneousJoinProvider
     */
    public function testHomogeneousJoin($original, string $expected): void
    {
        $this->assertEquals(
            $expected,
            Sentence::homogeneousJoin($original)
        );
    }

    public function homogeneousJoinProvider(): array
    {
        return [
            [[], ''],
            [['a'], 'a'],
            [['a', 'b'], 'a и b'],
            [['a', 'b', 'c'], 'a, b и c'],
            [Collection::collect(1, 2, 3, 4), '1, 2, 3 и 4'],
        ];
    }

    public function testHomogeneousJoinAlternativeDelimiters(): void
    {
        $this->assertEquals(
            'a.b-c',
            Sentence::homogeneousJoin(['a', 'b', 'c'], '.', '-')
        );
    }

    /**
     * @dataProvider terminateProvider
     */
    public function testTerminate(string $original, string $expected): void
    {
        $this->assertEquals(
            $expected,
            Sentence::terminate($original)
        );
    }

    public function terminateProvider(): array
    {
        return [
            ['abc', 'abc.'],
            ['abc.', 'abc.'],
            ['abc..', 'abc.'],
            ['abc...', 'abc...'],
            ['abc....', 'abc...'],
            ['abc!', 'abc!'],
            ['abc?', 'abc?'],
        ];
    }

    /**
     * @dataProvider buildCasedProvider
     */
    public function testBuildCased($source, bool $terminate, string $expected): void
    {
        $this->assertEquals(
            $expected,
            Sentence::buildCased($source, $terminate)
        );
    }

    public function buildCasedProvider(): array
    {
        return [
            'null' => [null, false, ''],
            'null_terminated' => [null, true, ''],
            'empty' => [[], false, ''],
            'empty_terminated' => [[], true, ''],
            'array_one_word' => [
                ['aaa'],
                false,
                'Aaa',
            ],
            'array_one_word_terminated' => [
                ['aaa'],
                true,
                'Aaa.',
            ],
            'array_many_words' => [
                ['aaa', 'Bbb', 'Ccc'],
                false,
                'Aaabbbccc',
            ],
            'array_many_words_terminated' => [
                ['aaa', 'Bbb', 'Ccc'],
                true,
                'Aaabbbccc.',
            ],
            'arrayable_one_word' => [
                Collection::collect('aaa'),
                false,
                'Aaa',
            ],
            'arrayable_one_word_terminated' => [
                Collection::collect('aaa'),
                true,
                'Aaa.',
            ],
            'arrayable_many_words' => [
                Collection::collect('aaa', 'Bbb', 'Ccc'),
                false,
                'Aaabbbccc',
            ],
            'arrayable_many_words_terminated' => [
                Collection::collect('aaa', 'Bbb', 'Ccc'),
                true,
                'Aaabbbccc.',
            ],
        ];
    }
}
