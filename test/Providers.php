<?php

namespace test\eLife\Journal;

use Traversable;

trait Providers
{
    final public static function incorrectSlugProvider() : Traversable
    {
        yield 'no slug' => [];
        yield 'no slug with query string' => [null, 'foo'];
        yield 'wrong slug' => ['/foo'];
        yield 'wrong slug with query string' => ['/foo', 'bar'];
    }

    final protected function stringProvider(string ...$strings) : Traversable
    {
        foreach ($strings as $string) {
            yield $string => [$string];
        }
    }

    final protected static function arrayProvider(array $array) : Traversable
    {
        foreach ($array as $key => $value) {
            $values = (array) $value;
            array_unshift($values, $key);

            yield $key => $values;
        }
    }
}
