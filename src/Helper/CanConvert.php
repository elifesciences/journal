<?php

namespace eLife\Journal\Helper;

use eLife\Patterns\ViewModel;

trait CanConvert
{
    use HasViewModelConverter;

    final protected function willConvertTo(string $viewModel = null, array $context = []) : callable
    {
        return function ($object) use ($viewModel, $context) {
            return $this->convertTo($object, $viewModel, $context);
        };
    }

    final protected function convertTo($object, string $viewModel = null, array $context = []) : ViewModel
    {
        return $this->getViewModelConverter()->convert($object, $viewModel, $context);
    }
}
