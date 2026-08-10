<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\BookChapterReference;
use eLife\Journal\ViewModel\Converter\Reference\BookChapterReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class BookChapterReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = BookChapterReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new BookChapterReferenceConverter();
    }
}
