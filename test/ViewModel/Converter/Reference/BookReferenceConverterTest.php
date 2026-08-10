<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookReference;
use eLife\Journal\ViewModel\Converter\Reference\BookReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class BookReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = BookReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new BookReferenceConverter();
    }
}
