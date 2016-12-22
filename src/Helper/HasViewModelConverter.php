<?php

namespace eLife\Journal\Helper;

use eLife\Journal\ViewModel\Converter\ViewModelConverter;

trait HasViewModelConverter
{
    abstract protected function getViewModelConverter() : ViewModelConverter;
}
