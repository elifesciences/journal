<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\Journal\ViewModel\Converter\Reference\BookReferenceConverter;

final class BookReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = BookReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BookReferenceConverter();
    }
}
