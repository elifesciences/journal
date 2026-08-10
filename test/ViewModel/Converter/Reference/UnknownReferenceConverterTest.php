<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\UnknownReference;
use eLife\Journal\ViewModel\Converter\Reference\UnknownReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class UnknownReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = UnknownReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new UnknownReferenceConverter();
    }
}
