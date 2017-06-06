<?php

namespace test\eLife\Journal\Helper;

use Assert\Assertion;
use eLife\Journal\Helper\LicenceUri;
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class LicenceUriTest extends PHPUnit_Framework_TestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_provides_a_default_uri()
    {
        Assertion::url(LicenceUri::default());
    }

    /**
     * @test
     * @dataProvider validModelProvider
     */
    public function it_providers_a_uri_for_a_code(string $code)
    {
        Assertion::url(LicenceUri::forCode($code));
    }

    public function validModelProvider() : Traversable
    {
        return $this->stringProvider('CC0-1.0', 'CC-BY-1.0', 'CC-BY-2.0', 'CC-BY-2.5', 'CC-BY-3.0', 'CC-BY-4.0');
    }

    /**
     * @test
     */
    public function it_fails_on_an_invalid_code()
    {
        $this->expectException(InvalidArgumentException::class);

        LicenceUri::forCode('foo');
    }
}
