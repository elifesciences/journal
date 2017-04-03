<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\WebReference;
use eLife\Journal\ViewModel\Converter\Reference\WebReferenceConverter;

final class WebReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = WebReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new WebReferenceConverter();
    }
}
