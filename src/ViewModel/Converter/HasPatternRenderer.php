<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

trait HasPatternRenderer
{
    abstract protected function getPatternRenderer() : PatternRenderer;

    final private function render(ViewModel ...$viewModels)
    {
        return implode('', array_map(function (ViewModel $viewModel) {
            return $this->getPatternRenderer()->render($viewModel);
        }, $viewModels));
    }
}
