<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\Journal\ViewModel\Converter\Reference\JournalReferenceConverter;

final class JournalReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = JournalReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new JournalReferenceConverter();
    }
}
