<?php

namespace eLife\Journal\Helper;

final class MediaTypes
{
    private static $types = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/svg+xml' => 'svg',
    ];

    public static function toExtension(string $mediaType) : string
    {
        return self::$types[$mediaType];
    }
}
