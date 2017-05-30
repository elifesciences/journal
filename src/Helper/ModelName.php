<?php

namespace eLife\Journal\Helper;

use Assert\Assertion;

final class ModelName
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
            'singular' => 'Feature Article',
            'plural' => 'Feature Articles',
        ],
        'insight' => [
            'singular' => 'Insight',
            'plural' => 'Insights',
        ],
        'research-advance' => [
            'singular' => 'Research Advance',
            'plural' => 'Research Advances',
        ],
        'research-article' => [
            'singular' => 'Research Article',
            'plural' => 'Research Articles',
        ],
        'retraction' => [
            'singular' => 'Retraction',
            'plural' => 'Retractions',
        ],
        'registered-report' => [
            'singular' => 'Registered Report',
            'plural' => 'Registered Reports',
        ],
        'replication-study' => [
            'singular' => 'Replication Study',
            'plural' => 'Replication Studies',
        ],
        'scientific-correspondence' => [
            'singular' => 'Scientific Correspondence',
            'plural' => 'Scientific Correspondences',
        ],
        'short-report' => [
            'singular' => 'Short Report',
            'plural' => 'Short Reports',
        ],
        'tools-resources' => [
            'singular' => 'Tools and Resources',
            'plural' => 'Tools and Resources',
        ],
        'blog-article' => [
            'singular' => 'Inside eLife',
            'plural' => 'Inside eLife',
        ],
        'collection' => [
            'singular' => 'Collection',
            'plural' => 'Collections',
        ],
        'event' => [
            'singular' => 'Event',
            'plural' => 'Events',
        ],
        'labs-post' => [
            'singular' => 'Labs Post',
            'plural' => 'Labs Posts',
        ],
        'interview' => [
            'singular' => 'Interview',
            'plural' => 'Interviews',
        ],
        'podcast-episode' => [
            'singular' => 'Podcast',
            'plural' => 'Podcasts',
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
