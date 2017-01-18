<?php

namespace eLife\Journal\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

final class VersionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions() : array
    {
        return [
            new ExpressionFunction(
                'version',
                function () {
                    return '$this->get("kernel")->getVersion()';
                },
                function (array $variables) {
                    return $variables['container']->get('kernel')->getVersion();
                }
            ),
        ];
    }
}
