<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\DataReference;
use eLife\Journal\ViewModel\Converter\Reference\DataReferenceConverter;

final class DataReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = DataReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new DataReferenceConverter();
    }
}
