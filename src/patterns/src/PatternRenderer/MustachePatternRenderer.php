<?php

namespace eLife\Patterns\PatternRenderer;

use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;
use Mustache_Engine;

final class MustachePatternRenderer implements PatternRenderer
{
    private $mustache;

    public function __construct(Mustache_Engine $mustache)
    {
        $this->mustache = $mustache;
    }

    public function render(ViewModel ...$viewModels) : string
    {
        return implode('', array_map(function (ViewModel $viewModel) {
            return $this->mustache->render($viewModel->getTemplateName(), $viewModel);
        }, $viewModels));
    }
}
