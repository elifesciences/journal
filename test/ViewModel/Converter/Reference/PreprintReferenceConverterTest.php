<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PreprintReference;
use eLife\Journal\ViewModel\Converter\Reference\PreprintReferenceConverter;

final class PreprintReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = PreprintReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PreprintReferenceConverter();
    }
}
