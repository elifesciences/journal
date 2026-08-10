<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ThesisReference;
use eLife\Journal\ViewModel\Converter\Reference\ThesisReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class ThesisReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = ThesisReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ThesisReferenceConverter();
    }
}
