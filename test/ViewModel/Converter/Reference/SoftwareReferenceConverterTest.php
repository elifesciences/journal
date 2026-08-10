<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\SoftwareReference;
use eLife\Journal\ViewModel\Converter\Reference\SoftwareReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class SoftwareReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = SoftwareReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SoftwareReferenceConverter();
    }
}
