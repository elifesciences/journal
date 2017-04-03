<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\UnknownReference;
use eLife\Journal\ViewModel\Converter\Reference\UnknownReferenceConverter;

final class UnknownReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = UnknownReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new UnknownReferenceConverter();
    }
}
