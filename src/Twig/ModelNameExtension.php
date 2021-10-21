<?php

namespace eLife\Journal\Twig;

use eLife\Journal\Helper\ModelName;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ModelNameExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'model_name',
                [$this, 'getModelName']
            ),
        ];
    }

    public function getModelName(string $modelName) : string
    {
        return ModelName::singular($modelName);
    }
}
