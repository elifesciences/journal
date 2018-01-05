<?php

namespace test\eLife\Journal\Helper;

use eLife\Journal\Helper\LicenceUri;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use test\eLife\Journal\Providers;
use Traversable;

final class LicenceUriTest extends TestCase
{
    use Providers;

    /**
     * @test
     */
    public function it_provides_a_default_uri()
    {
        $this->assertSame('https://creativecommons.org/licenses/by/4.0/', LicenceUri::default());
    }

    /**
     * @test
     * @dataProvider validModelProvider
     */
    public function it_providers_a_uri_for_a_code(string $code, string $uri)
    {
        $this->assertSame($uri, LicenceUri::forCode($code));
    }

    public function validModelProvider() : Traversable
    {
        return $this->arrayProvider([
            'CC0-1.0' => 'https://creativecommons.org/publicdomain/zero/1.0/',
            'CC-BY-1.0' => 'https://creativecommons.org/licenses/by/1.0/',
            'CC-BY-2.0' => 'https://creativecommons.org/licenses/by/2.0/',
            'CC-BY-2.5' => 'https://creativecommons.org/licenses/by/2.5/',
            'CC-BY-3.0' => 'https://creativecommons.org/licenses/by/3.0/',
            'CC-BY-4.0' => 'https://creativecommons.org/licenses/by/4.0/',
        ]);
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
