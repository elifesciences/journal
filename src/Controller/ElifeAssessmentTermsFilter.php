<?php

namespace eLife\Journal\Controller;

class ElifeAssessmentTermsFilter
{
    private static $significanceTerms = [
        'landmark',
        'fundamental',
        'important',
        'valuable',
        'useful',
        'not-assigned',
    ];

    private static $strengthTerms = [
        'exceptional',
        'compelling',
        'convincing',
        'solid',
        'incomplete',
        'inadequate',
    ];

    public static function fromMinimumSignificance(string $minimumSignificance = null): array
    {
        return self::fromMinimumTerm($minimumSignificance, self::$significanceTerms);
    }

    public static function fromMinimumStrength(string $minimumStrength = null): array
    {
        return self::fromMinimumTerm($minimumStrength, self::$strengthTerms);
    }

    private static function fromMinimumTerm(string $minimumTerm = null, array $availableTerms): array
    {
        $results = [];

        foreach ($availableTerms as $term) {
            $results[] = $term;
            if ($term === $minimumTerm) {
                break;
            }
        }

        return $results;
    }
}
