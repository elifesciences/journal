<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\TeaserTerms;

class TeaserTermsBuilder
{
    public function build(ElifeAssessment $elifeAssessment)
    {
        $buildTermWithCorrectCasing = function (string $termValue) {
            return new ViewModel\Term(ucfirst($termValue));
        };

        $significance = array_map(
            $buildTermWithCorrectCasing,
            $elifeAssessment->getSignificance() ?? []
        );

        $strength = array_map(
            $buildTermWithCorrectCasing,
            $elifeAssessment->getStrength() ?? []
        );
        return new TeaserTerms(array_merge($significance, $strength));
    }
}
