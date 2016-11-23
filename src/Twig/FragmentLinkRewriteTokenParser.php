<?php

namespace eLife\Journal\Twig;

use Twig_Node;
use Twig_Token;
use Twig_TokenParser;

final class FragmentLinkRewriteTokenParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token) : Twig_Node
    {
        $lineno = $token->getLine();

        $link = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideForEnd'], true);
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new FragmentLinkRewriteNode($body, $link, $lineno, $this->getTag());
    }

    public function decideForEnd(Twig_Token $token) : bool
    {
        return $token->test('end_'.$this->getTag());
    }

    public function getTag() : string
    {
        return 'fragment_link_rewrite';
    }
}
