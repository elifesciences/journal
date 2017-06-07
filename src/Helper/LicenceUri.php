<?php

namespace eLife\Journal\Helper;

use Assert\Assertion;

final class LicenceUri
{
    private static $licences = [
        'CC0-1.0' => 'https://creativecommons.org/publicdomain/zero/1.0/',
        'CC-BY-1.0' => 'https://creativecommons.org/licenses/by/1.0/',
        'CC-BY-2.0' => 'https://creativecommons.org/licenses/by/2.0/',
        'CC-BY-2.5' => 'https://creativecommons.org/licenses/by/2.5/',
        'CC-BY-3.0' => 'https://creativecommons.org/licenses/by/3.0/',
        'CC-BY-4.0' => 'https://creativecommons.org/licenses/by/4.0/',
    ];

    private function __construct()
    {
    }

    public static function forCode(string $code) : string
    {
        Assertion::keyExists(self::$licences, $code);

        return self::$licences[$code];
    }

    public static function default() : string
    {
        return self::forCode('CC-BY-4.0');
    }
}
