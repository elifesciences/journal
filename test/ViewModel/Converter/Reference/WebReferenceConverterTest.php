<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\WebReference;
use eLife\Journal\ViewModel\Converter\Reference\WebReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class WebReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = WebReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new WebReferenceConverter();
    }
}
