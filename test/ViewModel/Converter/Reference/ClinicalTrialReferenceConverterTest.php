<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ClinicalTrialReference;
use eLife\Journal\ViewModel\Converter\Reference\ClinicalTrialReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class ClinicalTrialReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = ClinicalTrialReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ClinicalTrialReferenceConverter();
    }
}
