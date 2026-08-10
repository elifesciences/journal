<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PatentReference;
use eLife\Journal\ViewModel\Converter\Reference\PatentReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class PatentReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = PatentReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PatentReferenceConverter();
    }
}
