<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\Patterns\PatternRenderer;

trait HasPatternRenderer
{
    abstract protected function getPatternRenderer() : PatternRenderer;
}
