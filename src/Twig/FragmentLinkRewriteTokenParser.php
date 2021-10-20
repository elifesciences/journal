<?php

namespace eLife\Journal\Twig;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

final class FragmentLinkRewriteTokenParser extends AbstractTokenParser
{
    public function parse(Token $token) : Node
    {
        $lineno = $token->getLine();

        $link = $this->parser->getExpressionParser()->parseExpression();
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideForEnd'], true);
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new FragmentLinkRewriteNode($body, $link, $lineno, $this->getTag());
    }

    public function decideForEnd(Token $token) : bool
    {
        return $token->test('end_'.$this->getTag());
    }

    public function getTag() : string
    {
        return 'fragment_link_rewrite';
    }
}
