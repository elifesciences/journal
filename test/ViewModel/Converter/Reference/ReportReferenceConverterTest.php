<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ReportReference;
use eLife\Journal\ViewModel\Converter\Reference\ReportReferenceConverter;

final class ReportReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = ReportReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ReportReferenceConverter();
    }
}
