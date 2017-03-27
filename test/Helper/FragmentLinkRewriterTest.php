<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\FragmentLinkRewriter;
use PHPUnit_Framework_TestCase;

final class FragmentLinkRewriterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_rewrites_fragment_links()
    {
        $rewriter = new FragmentLinkRewriter();

        $input = '<html><body id="this-id">Foo <a href="#this-id">bar</a> <a href="#other-id">o\'baz</a> qux</body>';
        $expected = '<html><body id="this-id">Foo <a href="#this-id">bar</a> <a href="/content/1/e00001/figures#other-id">o\'baz</a> qux</body>';

        $this->assertSame($expected, $rewriter->rewrite($input, '/content/1/e00001/figures'));
    }
}
