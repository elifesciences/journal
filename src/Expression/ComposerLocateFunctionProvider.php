<?php

namespace eLife\Journal\Expression;

use ComposerLocator;
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
                    return sprintf('\ComposerLocator::getPath(%s)', $name);
                },
                function ($arguments, $name) {
                    return ComposerLocator::getPath($name);
                }
            ),
        ];
    }
}
