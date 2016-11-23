<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Helper\FragmentLinkRewriter;
use eLife\Journal\Twig\FragmentLinkRewriterExtension;
use PHPUnit_Framework_TestCase;
use Twig_Environment;
use Twig_ExtensionInterface;
use Twig_Loader_Array;

final class FragmentLinkRewriterExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new FragmentLinkRewriterExtension(new FragmentLinkRewriter());

        $this->assertInstanceOf(Twig_ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_rewrites_fragment_links()
    {
        $twigLoader = new Twig_Loader_Array(['foo' => '<a href="#foo">foo</a> {% fragment_link_rewrite "uri" %}<a href="#bar">bar</a>{% end_fragment_link_rewrite %}']);
        $twig = new Twig_Environment($twigLoader);
        $twig->addExtension(new FragmentLinkRewriterExtension(new FragmentLinkRewriter()));

        $this->assertSame('<a href="#foo">foo</a> <a href="uri#bar">bar</a>', $twig->render('foo'));
    }
}
