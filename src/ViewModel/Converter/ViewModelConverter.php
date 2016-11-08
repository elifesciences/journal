<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Patterns\ViewModel;

interface ViewModelConverter
{
    public function convert($object, string $viewModel = null, array $context = []) : ViewModel;

    public function supports($object, string $viewModel = null, array $context = []) : bool;
}
