<?php

namespace test\eLife\Journal\Expression;

use eLife\Journal\Expression\TimeFunctionProvider;
use PHPUnit_Framework_TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class TimeFunctionProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_turns_strings_into_times()
    {
        $expressionLanguage = new ExpressionLanguage();

        $expressionLanguage->registerProvider(new TimeFunctionProvider());

        $this->assertSame(
            strtotime('2018-01-01T00:00:00Z'),
            $expressionLanguage->evaluate('strtotime("2018-01-01T00:00:00Z")')
        );
    }
}
