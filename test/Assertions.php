<?php

namespace test\eLife\Journal;

use GuzzleHttp\Psr7\UriNormalizer;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\Utils;

trait Assertions
{
    /**
     * @param string|UriInterface $expected
     * @param string|UriInterface $actual
     */
    final protected function assertSameUri($expected, $actual, string $message = '')
    {
        $flags = UriNormalizer::PRESERVING_NORMALIZATIONS | UriNormalizer::SORT_QUERY_PARAMETERS;

        $normalizedExpected = UriNormalizer::normalize(Utils::uriFor($expected), $flags)->__toString();
        $normalizedActual = UriNormalizer::normalize(Utils::uriFor($actual), $flags)->__toString();

        $this->assertEquals($normalizedExpected, $normalizedActual, $message);
    }
}
