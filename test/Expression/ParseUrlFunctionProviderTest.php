<?php

namespace test\eLife\Journal\Expression;

use eLife\Journal\Expression\ParseUrlFunctionProvider;
use PHPUnit_Framework_TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ParseUrlFunctionProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_parses_urls()
    {
        $expressionLanguage = new ExpressionLanguage();

        $expressionLanguage->registerProvider(new ParseUrlFunctionProvider());

        $this->assertSame(
            parse_url('http://www.example.com/foo'),
            $expressionLanguage->evaluate('parse_url("http://www.example.com/foo")')
        );
    }

    /**
     * @test
     */
    public function it_parses_url_components()
    {
        $expressionLanguage = new ExpressionLanguage();

        $expressionLanguage->registerProvider(new ParseUrlFunctionProvider());

        $this->assertSame(
            '/foo',
            $expressionLanguage->evaluate('parse_url("http://www.example.com/foo", constant("PHP_URL_PATH"))')
        );
    }
}
