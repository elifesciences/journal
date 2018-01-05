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

    public static function prettyList(string ...$items) : string
    {
        $last = array_slice($items, -1);
        $first = implode(', ', array_slice($items, 0, -1));
        $both = array_filter(array_merge([$first], $last), 'strlen');

        return implode(' and ', $both);
    }
}
