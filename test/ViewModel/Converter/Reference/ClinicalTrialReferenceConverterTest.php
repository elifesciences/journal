<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\Journal\ViewModel\Converter\Reference\ClinicalTrialReferenceConverter;

final class ClinicalTrialReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = ClinicalTrialReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ClinicalTrialReferenceConverter();
    }
}
