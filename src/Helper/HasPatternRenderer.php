<?php

namespace eLife\Journal\Helper;

use eLife\Patterns\PatternRenderer;

trait HasPatternRenderer
{
    abstract protected function getPatternRenderer() : PatternRenderer;
}
