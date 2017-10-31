<?php

namespace eLife\Journal\Helper;

use Assert\Assertion;

final class MediaTypes
{
    private static $types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/svg+xml' => 'svg',
        'image/webp' => 'webp',
    ];

    public static function toExtension(string $mediaType) : string
    {
        Assertion::keyExists(self::$types, $mediaType);

        return self::$types[$mediaType];
    }
}
