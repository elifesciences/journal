<?php

namespace eLife\Journal\Helper;

use Assert\Assertion;

final class ArticleType
{
    private static $types = [
        'correction' => [
            'singular' => 'Correction',
            'plural' => 'Corrections',
        ],
        'editorial' => [
            'singular' => 'Editorial',
            'plural' => 'Editorials',
        ],
        'feature' => [
            'singular' => 'Feature article',
            'plural' => 'Feature articles',
        ],
        'insight' => [
            'singular' => 'Insight',
            'plural' => 'Insights',
        ],
        'research-advance' => [
            'singular' => 'Research advance',
            'plural' => 'Research advances',
        ],
        'research-article' => [
            'singular' => 'Research article',
            'plural' => 'Research articles',
        ],
        'research-exchange' => [
            'singular' => 'Research exchange',
            'plural' => 'Research exchanges',
        ],
        'retraction' => [
            'singular' => 'Retraction',
            'plural' => 'Retractions',
        ],
        'registered-report' => [
            'singular' => 'Registered report',
            'plural' => 'Registered reports',
        ],
        'replication-study' => [
            'singular' => 'Replication study',
            'plural' => 'Replication studies',
        ],
        'short-report' => [
            'singular' => 'Short report',
            'plural' => 'Short reports',
        ],
        'tools-resources' => [
            'singular' => 'Tools and resources',
            'plural' => 'Tools and resources',
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
