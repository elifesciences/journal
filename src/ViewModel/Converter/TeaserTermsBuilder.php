<?php

namespace eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\ElifeAssessment;
use eLife\Patterns\ViewModel;
use eLife\Patterns\ViewModel\TeaserTerms;

class TeaserTermsBuilder
{
    public function build(ElifeAssessment $elifeAssessment)
    {
        $significance = array_map(
            function ($significanceValue) {
                return new ViewModel\Term(ucfirst($significanceValue));
            },
            $elifeAssessment->getSignificance() ?? []
        );

        $strength = array_map(
            function ($strengthValue) {
                return new ViewModel\Term(ucfirst($strengthValue));
            },
            $elifeAssessment->getStrength() ?? []
        );
        return new TeaserTerms(array_merge($significance, $strength));
    }
}
