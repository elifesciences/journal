<?php

namespace test\eLife\Journal;

use GuzzleHttp\Psr7\UriNormalizer;
use Psr\Http\Message\UriInterface;
use function GuzzleHttp\Psr7\uri_for;

trait Assertions
{
    /**
     * @param string|UriInterface $expected
     * @param string|UriInterface $actual
     */
    final protected function assertSameUri($expected, $actual, string $message = '')
    {
        $flags = NormalizingStorageAdapter::URI_FLAGS;

        $normalizedExpected = UriNormalizer::normalize(uri_for($expected), $flags)->__toString();
        $normalizedActual = UriNormalizer::normalize(uri_for($actual), $flags)->__toString();

        $this->assertEquals($normalizedExpected, $normalizedActual, $message);
    }
}
