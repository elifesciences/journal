<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ThesisReference;
use eLife\Journal\ViewModel\Converter\Reference\ThesisReferenceConverter;

final class ThesisReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = ThesisReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ThesisReferenceConverter();
    }
}
