<?php

namespace eLife\Journal\Helper;

final class ModelRelationship
{
    private static $defaultRelated = 'Related to';
    private static $defaultUnrelated = 'Of interest';
    private static $fromRelationship = [
        'registered-report' => [
            'to' => [
                'external-article',
            ],
            'text' => 'Original article',
        ],
        'research-advance' => [
            'to' => [
                'research-advance',
                'research-article',
                'research-communication',
                'scientific-correspondence',
                'short-report',
                'tools-resources',
                'replication-study',
            ],
            'text' => 'Builds upon',
        ],
    ];

    private static $toRelationship = [
        'collection' => 'Part of collection',
        'podcast-episode-chapter' => 'Discussed in',
        'research-advance' => [
            'from' => [
                'research-advance',
                'research-article',
                'research-communication',
                'scientific-correspondence',
                'short-report',
                'tools-resources',
                'replication-study',
            ],
            'text' => 'Built upon by',
        ],
    ];

    private function __construct()
    {
    }

    public static function get(string $from, string $to, bool $related = false) : string
    {
        if (!$related) {
            return self::$defaultUnrelated;
        }

        if (!empty(self::$fromRelationship[$from])) {
            if (is_string(self::$fromRelationship[$from])) {
                return self::$fromRelationship[$from];
            }

            if (in_array($to, self::$fromRelationship[$from]['to'])) {
                return self::$fromRelationship[$from]['text'];
            }

            return self::$defaultRelated;
        }

        if (!empty(self::$toRelationship[$to])) {
            if (is_string(self::$toRelationship[$to])) {
                return self::$toRelationship[$to];
            }

            if (in_array($from, self::$toRelationship[$to]['from'])) {
                return self::$toRelationship[$to]['text'];
            }

            return self::$defaultRelated;
        }

        return self::$defaultRelated;
    }
}
