<?php

namespace eLife\Journal\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class ParseUrlFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions() : array
    {
        return [
            new ExpressionFunction(
                'parse_url',
                function ($string, $component = '-1') {
                    return sprintf('parse_url(%s, %s)', $string, $component);
                },
                function ($arguments, $string, $component = '-1') {
                    return parse_url($string, $component);
                }
            ),
        ];
    }
}
