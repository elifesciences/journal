<?php

namespace eLife\Journal\Mustache;

final class DynamicValueHelper
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __invoke()
    {
        return $this->value;
    }
}
