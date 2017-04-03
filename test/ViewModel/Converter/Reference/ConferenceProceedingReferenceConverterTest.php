<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\ConferenceProceedingReference;
use eLife\Journal\ViewModel\Converter\Reference\ConferenceProceedingReferenceConverter;

final class ConferenceProceedingReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = ConferenceProceedingReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ConferenceProceedingReferenceConverter();
    }
}
