<?php

namespace eLife\Patterns\PatternRenderer;

use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class CallbackPatternRenderer implements PatternRenderer
{
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public function render(ViewModel ...$viewModels) : string
    {
        return call_user_func($this->callback, ...$viewModels);
    }
}
