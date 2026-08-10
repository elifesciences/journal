<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\Humanizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class HumanizerTest extends TestCase
{
    use Providers;

    #[Test]
    #[DataProvider('stringProvider')]
    public function it_humanizes_strings(string $input, string $expected)
    {
        $this->assertSame($expected, Humanizer::humanize($input));
    }

    public static function stringProvider() : Traversable
    {
        return self::arrayProvider([
            'foo' => 'Foo',
            'foo_bar' => 'Foo bar',
            'foo-bar' => 'Foo bar',
        ]);
    }


    #[Test]
    #[DataProvider('listProvider')]
    public function it_prettifies_lists(string $expected, ...$input)
    {
        $this->assertSame($expected, Humanizer::prettyList(...$input));
    }

    public static function listProvider() : Traversable
    {
        return self::arrayProvider([
            '' => [],
            'foo' => ['foo'],
            'foo and bar' => ['foo', 'bar'],
            'foo, bar and baz' => ['foo', 'bar', 'baz'],
            'foo, bar, baz and qux' => ['foo', 'bar', 'baz', 'qux'],
        ]);
    }
}
