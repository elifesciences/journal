<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PatentReference;
use eLife\Journal\ViewModel\Converter\Reference\PatentReferenceConverter;

final class PatentReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = PatentReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PatentReferenceConverter();
    }
}
