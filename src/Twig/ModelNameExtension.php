<?php

namespace eLife\Journal\Twig;

use eLife\Journal\Helper\ModelName;
use Twig_Extension;
use Twig_Function;

final class ModelNameExtension extends Twig_Extension
{
    public function getFunctions()
    {
        return [
            new Twig_Function(
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
