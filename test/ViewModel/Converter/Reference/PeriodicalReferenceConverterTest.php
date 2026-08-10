<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PeriodicalReference;
use eLife\Journal\ViewModel\Converter\Reference\PeriodicalReferenceConverter;
use PHPUnit\Framework\Attributes\Before;

final class PeriodicalReferenceConverterTest extends ReferenceConverterTestCase
{
    protected string $referenceClass = PeriodicalReference::class;

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new PeriodicalReferenceConverter();
    }
}
