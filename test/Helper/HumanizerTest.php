<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\Humanizer;
use PHPUnit_Framework_TestCase;
use Traversable;

final class HumanizerTest extends PHPUnit_Framework_TestCase
{
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
        $inputs = [
            'foo' => 'Foo',
            'foo_bar' => 'Foo bar',
            'foo-bar' => 'Foo bar',
        ];

        foreach ($inputs as $input => $expected) {
            yield $input => [$input, $expected];
        }
    }
}
