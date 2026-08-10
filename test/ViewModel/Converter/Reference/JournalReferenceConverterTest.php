<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\JournalReference;
use eLife\Journal\ViewModel\Converter\Reference\JournalReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class JournalReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = JournalReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new JournalReferenceConverter();
    }
}
