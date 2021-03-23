<?php

namespace eLife\Journal\ViewModel\Converter\Reference;

trait HasLabel
{
    private function label() : string
    {
        return self::LABEL;
    }
}
