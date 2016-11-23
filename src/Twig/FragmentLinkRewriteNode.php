<?php

namespace eLife\Journal\Twig;

use Twig_Compiler;
use Twig_Node;
use Twig_Node_Expression;

final class FragmentLinkRewriteNode extends Twig_Node
{
    public function __construct(Twig_Node $body, Twig_Node $link, $lineno)
    {
        parent::__construct(['body' => $body, 'link' => $link], [], $lineno, 'fragment_link_rewrite');
    }

    public function compile(Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        if (false === $this->getNode('link') instanceof Twig_Node_Expression) {
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
            ->write('echo $this->env->getExtension("fragment_link_rewriter")->rewrite(ob_get_clean(), $_fragmentLinkRewriteUri);');
    }
}
