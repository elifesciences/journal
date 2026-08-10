<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PreprintReference;
use eLife\Journal\ViewModel\Converter\Reference\PreprintReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class PreprintReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = PreprintReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PreprintReferenceConverter();
    }
}
