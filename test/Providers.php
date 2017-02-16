<?php

namespace test\eLife\Journal;

use Traversable;

trait Providers
{
    final protected function stringProvider(string ...$strings) : Traversable
    {
        foreach ($strings as $string) {
            yield $string => [$string];
        }
    }

    final protected function arrayProvider(array $array) : Traversable
    {
        foreach ($array as $key => $value) {
            yield $key => [$key, $value];
        }
    }
}
