<?php

namespace eLife\Journal\Twig;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

#[YieldReady]
final class FragmentLinkRewriteNode extends Node
{
    public function __construct(Node $body, Node $link, int $lineno)
    {
        parent::__construct(['body' => $body, 'link' => $link], [], $lineno);
    }

    public function compile(Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $useYield = $compiler->getEnvironment()->useYield();

        $compiler->write('$_fragmentLinkRewriteUri = ');

        if (false === $this->getNode('link') instanceof AbstractExpression) {
            $this->compileCapture($compiler, 'link', $useYield);
        } else {
            $compiler->subcompile($this->getNode('link'));
        }

        $compiler->raw(';'.PHP_EOL);

        $compiler->write('$_fragmentLinkRewriteBody = ');
        $this->compileCapture($compiler, 'body', $useYield);
        $compiler->raw(';'.PHP_EOL);

        $compiler
            ->write('yield ')
            ->raw('$this->env->getExtension("'.FragmentLinkRewriterExtension::class.'")->rewrite($_fragmentLinkRewriteBody, $_fragmentLinkRewriteUri);')
            ->raw(PHP_EOL);
    }

    private function compileCapture(Compiler $compiler, string $nodeName, bool $useYield) : void
    {
        $compiler
            ->raw($useYield ? "implode('', iterator_to_array(" : '\Twig\Extension\CoreExtension::captureOutput(')
            ->raw('(function () use (&$context, $macros, $blocks) {'.PHP_EOL)
            ->indent()
            ->subcompile($this->getNode($nodeName))
            ->write('return; yield;'.PHP_EOL)
            ->outdent()
            ->write('})()');

        if ($useYield) {
            $compiler->raw(', false))');
        } else {
            $compiler->raw(')');
        }
    }
}
