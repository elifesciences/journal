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
        if (self::checkIfTheTermsRelatedQueryIsEmpty($query)) {
            return true;
        }
        if ($query['includeOriginalModelPapers'] !== 'yes') {
            return false;
        }
        return true;
    }

    public static function fromMinimumSignificance(string $minimumSignificance = null, array $query): array
    {
        return self::fromMinimumTerm($minimumSignificance, self::$significanceTerms, self::decideWhetherToIncludeOldModelPapers($query));
    }

    public static function fromMinimumStrength(string $minimumStrength = null, array $query): array
    {
        return self::fromMinimumTerm($minimumStrength, self::$strengthTerms, self::decideWhetherToIncludeOldModelPapers($query));
    }

    private static function checkIfTheTermsRelatedQueryIsEmpty($query)
    {
        if (!isset($query['includeOriginalModelPapers']) && !isset($query['minimumSignificance']) && !isset($query['minimumStrength'])) {
            return true;
        }
        return false;
    }

    private static function fromMinimumTerm(string $minimumTerm = null, array $availableTerms, bool $includeOriginalModelPapers): array
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
