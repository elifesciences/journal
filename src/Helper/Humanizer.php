<?php

namespace eLife\Journal\Helper;

final class Humanizer
{
    private function __construct()
    {
    }

    public static function humanize(string $string) : string
    {
        return ucfirst(trim(strtolower(preg_replace(['/([A-Z])/', '/[_\-\s]+/'], ['_$1', ' '], $string))));
    }
}
