<?php

namespace eLife\Journal\Helper;

use Assert\Assertion;

final class PersonType
{
    private static $types = [
        'director' => [
            'singular' => 'Board of directors',
            'plural' => 'Board of directors',
        ],
        'executive' => [
            'singular' => 'Executive staff',
            'plural' => 'Executive staff',
        ],
        'leadership' => [
            'singular' => 'Leadership team',
            'plural' => 'Leadership team',
        ],
        'reviewing-editor' => [
            'singular' => 'Reviewing editor',
            'plural' => 'Reviewing editor',
        ],
        'senior-editor' => [
            'singular' => 'Senior editor',
            'plural' => 'Senior editors',
        ],
    ];

    private function __construct()
    {
    }

    public static function singular(string $id) : string
    {
        return self::getForType($id)['singular'];
    }

    public static function plural(string $id) : string
    {
        return self::getForType($id)['plural'];
    }

    private static function getForType(string $id) : array
    {
        Assertion::keyExists(self::$types, $id);

        return self::$types[$id];
    }
}
