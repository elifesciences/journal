<?php

namespace eLife\Journal\Twig;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

final class FragmentLinkRewriteNode extends Node
{
    public function __construct(Node $body, Node $link, int $lineno, string $tag = 'fragment_link_rewrite')
    {
        parent::__construct(['body' => $body, 'link' => $link], [], $lineno, $tag);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        if (false === $this->getNode('link') instanceof AbstractExpression) {
            $compiler
                ->write('ob_start();')
                ->raw(PHP_EOL)
                ->subcompile($this->getNode('link'))
                ->write('$_fragmentLinkRewriteUri = ob_get_clean()');
        } else {
            $compiler
                ->write('$_fragmentLinkRewriteUri = ')
                ->subcompile($this->getNode('link'));
        }

        $compiler
            ->raw(';'.PHP_EOL)
            ->write('ob_start();')
            ->raw(PHP_EOL)
            ->subcompile($this->getNode('body'))
            ->write('echo $this->env->getExtension("'.FragmentLinkRewriterExtension::class.'")->rewrite(ob_get_clean(), $_fragmentLinkRewriteUri);');
    }
}
