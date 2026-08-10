<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\DataReference;
use eLife\Journal\ViewModel\Converter\Reference\DataReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class DataReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = DataReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new DataReferenceConverter();
    }
}
