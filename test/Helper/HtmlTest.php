<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\Html;
use PHPUnit\Framework\TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class HtmlTest extends TestCase
{
    use Providers;

    /**
     * @test
     * @dataProvider stripElementProvider
     */
    public function it_strips_an_element(string $input, string $expected)
    {
        $this->assertSame($expected, Html::stripElement($input, 'a'));
    }

    public function stripElementProvider() : Traversable
    {
        return $this->arrayProvider([
            'foo' => 'foo',
            '<a>foo</a>' => 'foo',
            '<A>foo</A>' => 'foo',
            '<a href="#">foo</a>' => 'foo',
            '<a href="#" class="bar">foo</a>' => 'foo',
            '<a href="#">foo</a><a href="#">bar</a>' => 'foobar',
            '<b>foo</b>' => '<b>foo</b>',
            '<ab>foo</ab>' => '<ab>foo</ab>',
            '<a >foo</a>' => 'foo',
            '<a>foo</a><b>bar</b>' => 'foo<b>bar</b>',
            '<a>foo</a><b>bar</b><a>baz</a>' => 'foo<b>bar</b>baz',
            '<a href="#">foo</a><b>bar</b><a href="#">baz</a>' => 'foo<b>bar</b>baz',
            '<a>foo<b>bar</b></a>' => 'foo<b>bar</b>',
        ]);
    }
}
