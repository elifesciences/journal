<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\Humanizer;
use PHPUnit\Framework\TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class HumanizerTest extends TestCase
{
    use Providers;

    /**
     * @test
     * @dataProvider stringProvider
     */
    public function it_humanizes_strings(string $input, string $expected)
    {
        $this->assertSame($expected, Humanizer::humanize($input));
    }

    public function stringProvider() : Traversable
    {
        return $this->arrayProvider([
            'foo' => 'Foo',
            'foo_bar' => 'Foo bar',
            'foo-bar' => 'Foo bar',
        ]);
    }

    /**
     * @test
     * @dataProvider listProvider
     */
    public function it_prettifies_lists(string $expected, ...$input)
    {
        $this->assertSame($expected, Humanizer::prettyList(...$input));
    }

    public function listProvider() : Traversable
    {
        return $this->arrayProvider([
            '' => [],
            'foo' => ['foo'],
            'foo and bar' => ['foo', 'bar'],
            'foo, bar and baz' => ['foo', 'bar', 'baz'],
            'foo, bar, baz and qux' => ['foo', 'bar', 'baz', 'qux'],
        ]);
    }
}
