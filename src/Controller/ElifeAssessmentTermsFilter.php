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

    public static function decideWhetherToIncludeOldModelPapers(array $query): bool
    {
        if (!isset($query['includeOriginalModelPapers'])) {
            return true;
        }
        if ($query['includeOriginalModelPapers'] !== 'yes') {
            return false;
        }
        return true;
    }

    public static function fromMinimumSignificance(string $minimumSignificance = null, string $includeOriginalModelPapers = ''): array
    {
        return self::fromMinimumTerm($minimumSignificance, self::$significanceTerms, $includeOriginalModelPapers);
    }

    public static function fromMinimumStrength(string $minimumStrength = null, string $includeOriginalModelPapers = ''): array
    {
        return self::fromMinimumTerm($minimumStrength, self::$strengthTerms, $includeOriginalModelPapers);
    }

    private static function fromMinimumTerm(string $minimumTerm = null, array $availableTerms, string $includeOriginalModelPapers): array
    {
        $results = [];

        foreach ($availableTerms as $term) {
            $results[] = $term;
            if ($term === $minimumTerm) {
                break;
            }
        }
        if ($includeOriginalModelPapers) {
            $results[] = 'not-applicable';
        }

        return $results;
    }
}
