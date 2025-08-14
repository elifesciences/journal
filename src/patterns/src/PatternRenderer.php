<?php

namespace eLife\Patterns;

interface PatternRenderer
{
    public function render(ViewModel ...$viewModels) : string;
}
