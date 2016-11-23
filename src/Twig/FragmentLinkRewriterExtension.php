<?php

namespace eLife\Journal\Twig;

use eLife\Journal\Helper\FragmentLinkRewriter;
use Twig_Extension;

final class FragmentLinkRewriterExtension extends Twig_Extension
{
    private $rewriter;

    public function __construct(FragmentLinkRewriter $rewriter)
    {
        $this->rewriter = $rewriter;
    }

    public function getTokenParsers() : array
    {
        return [
            new FragmentLinkRewriteTokenParser(),
        ];
    }

    public function rewrite(string $html, string $alternateFragmentPage) : string
    {
        return $this->rewriter->rewrite($html, $alternateFragmentPage);
    }

    public function getName() : string
    {
        return 'fragment_link_rewriter';
    }
}
