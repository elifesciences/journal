<?php

namespace eLife\Journal\Helper;

final class ModelRelationship
{
    private static $fromRelationship = [
        'registered-report' => 'Original article',
        'research-advance' => 'Builds upon',
    ];

    private static $toRelationship = [
        'research-advance' => 'Built upon by',
    ];

    private function __construct()
    {
    }

    public static function get(string $from, string $to) : string
    {
        return self::$fromRelationship[$from] ?? (self::$toRelationship[$to] ?? 'Related to');
    }
}
