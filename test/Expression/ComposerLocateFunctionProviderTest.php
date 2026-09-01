<?php

namespace test\eLife\Journal\Expression;

use Composer\InstalledVersions;
use eLife\Journal\Expression\ComposerLocateFunctionProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class ComposerLocateFunctionProviderTest extends TestCase
{
    #[Test]
    public function it_locates_composer_packages()
    {
        $expressionLanguage = new ExpressionLanguage();

        $expressionLanguage->registerProvider(new ComposerLocateFunctionProvider());

        $this->assertSame(
            InstalledVersions::getInstallPath('elife/api'),
            $expressionLanguage->evaluate('composer_locate("elife/api")')
        );
    }
}
