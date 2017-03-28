<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\SoftwareReference;
use eLife\Journal\ViewModel\Converter\Reference\SoftwareReferenceConverter;

final class SoftwareReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = SoftwareReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SoftwareReferenceConverter();
    }
}
