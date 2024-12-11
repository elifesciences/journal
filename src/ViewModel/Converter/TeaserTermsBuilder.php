<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\TeaserTerms;

class TeaserTermsBuilder
{
    public function build(ElifeAssessment $elifeAssessment)
    {
        $significance = $this->buildTerms($elifeAssessment->getSignificance());
        $strength = $this->buildTerms($elifeAssessment->getStrength());
        return new TeaserTerms(array_merge($significance, $strength));
    }

    private function buildTerms($terms)
    {
        $buildTermWithCorrectCasing = function (string $termValue) {
            return new ViewModel\Term(ucfirst($termValue));
        };

        return array_map(
            $buildTermWithCorrectCasing,
            $terms ?? []
        );
    }
}
