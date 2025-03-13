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

    public static function fromMinimumSignificance(string $minimumSignificance = null, string $includeOriginalModelPapers = ''): array
    {
        $requiredSignificanceTerms = self::fromMinimumTerm($minimumSignificance, self::$significanceTerms);
        if ($includeOriginalModelPapers) {
            $requiredSignificanceTerms[] = 'not-applicable';
        }
        return $requiredSignificanceTerms;
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
