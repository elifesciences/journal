<?php

namespace test\eLife\Journal\Twig;

use eLife\Journal\Helper\FragmentLinkRewriter;
use eLife\Journal\Twig\FragmentLinkRewriterExtension;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Extension\ExtensionInterface;
use Twig\Loader\ArrayLoader;

final class FragmentLinkRewriterExtensionTest extends TestCase
{
    /**
     * @test
     */
    public function it_is_a_twig_extension()
    {
        $extension = new FragmentLinkRewriterExtension(new FragmentLinkRewriter());

        $this->assertInstanceOf(ExtensionInterface::class, $extension);
    }

    /**
     * @test
     * @depends it_is_a_twig_extension
     */
    public function it_rewrites_fragment_links()
    {
        $twigLoader = new ArrayLoader(['foo' => '<a href="#foo">foo</a> {% fragment_link_rewrite "uri" %}<a href="#bar">bar</a>{% end_fragment_link_rewrite %}']);
        $twig = new Environment($twigLoader);
        $twig->addExtension(new FragmentLinkRewriterExtension(new FragmentLinkRewriter()));

        $this->assertSame('<a href="#foo">foo</a> <a href="uri#bar">bar</a>', $twig->render('foo'));
    }
}
