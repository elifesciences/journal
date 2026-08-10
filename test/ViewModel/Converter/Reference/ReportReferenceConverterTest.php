<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ReportReference;
use eLife\Journal\ViewModel\Converter\Reference\ReportReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class ReportReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = ReportReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ReportReferenceConverter();
    }
}
