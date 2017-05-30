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
}
