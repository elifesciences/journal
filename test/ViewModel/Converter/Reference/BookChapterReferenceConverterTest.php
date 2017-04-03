<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\Journal\ViewModel\Converter\Reference\BookChapterReferenceConverter;

final class BookChapterReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = BookChapterReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BookChapterReferenceConverter();
    }
}
