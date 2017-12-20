<?php

namespace eLife\Journal\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class TimeFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions() : array
    {
        return [
            new ExpressionFunction(
                'strtotime',
                function ($time) {
                    return sprintf('\strtotime(%s)', $time);
                },
                function ($arguments, $time) {
                    return strtotime($time);
                }
            ),
        ];
    }
}
