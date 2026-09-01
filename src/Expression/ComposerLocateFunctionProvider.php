<?php

namespace eLife\Journal\Expression;

use Composer\InstalledVersions;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class ComposerLocateFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions() : array
    {
        return [
            new ExpressionFunction(
                'composer_locate',
                function ($name) {
                    return sprintf('\Composer\InstalledVersions::getInstallPath(%s)', $name);
                },
                function ($arguments, $name) {
                    return InstalledVersions::getInstallPath($name);
                }
            ),
        ];
    }
}
