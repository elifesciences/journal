<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use eLife\Journal\ViewModel\Converter\Reference\ConferenceProceedingReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class ConferenceProceedingReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = ConferenceProceedingReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ConferenceProceedingReferenceConverter();
    }
}
