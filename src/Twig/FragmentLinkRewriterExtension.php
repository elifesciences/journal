<?php

namespace eLife\Journal\Twig;

use eLife\Journal\Helper\FragmentLinkRewriter;
use Twig\Extension\AbstractExtension;

final class FragmentLinkRewriterExtension extends AbstractExtension
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
}
