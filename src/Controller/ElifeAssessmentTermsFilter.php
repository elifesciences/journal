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
    ];

    /**
     * @return array
     */
    public static function fromMinimumSignificance(string $minimumSignificance = null)
    {
        $results = [];

        foreach (self::$significanceTerms as $term) {
            $results[] = $term;
            if ($term === $minimumSignificance) {
                break;
            }
        }

        return $results;
    }
}
