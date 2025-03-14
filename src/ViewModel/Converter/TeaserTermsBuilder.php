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
        $arrayOfTerms = array_merge($significance, $strength);
        if (count($arrayOfTerms) === 0) {
            return null;
        }
        return new TeaserTerms($arrayOfTerms);
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
