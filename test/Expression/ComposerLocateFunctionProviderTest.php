<?php

namespace test\eLife\Journal\Expression;

use ComposerLocator;
use eLife\Journal\Expression\ComposerLocateFunctionProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ComposerLocateFunctionProviderTest extends TestCase
{
    /**
     * @test
     */
    public function it_locates_composer_packages()
    {
        $expressionLanguage = new ExpressionLanguage();

        $expressionLanguage->registerProvider(new ComposerLocateFunctionProvider());

        $this->assertSame(
            ComposerLocator::getPath('elife/api'),
            $expressionLanguage->evaluate('composer_locate("elife/api")')
        );
    }
}
