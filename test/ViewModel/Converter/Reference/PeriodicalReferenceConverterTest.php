<?php

namespace test\eLife\Journal\ViewModel\Converter\Reference;

use eLife\ApiSdk\Model\Reference\PeriodicalReference;
use eLife\Journal\ViewModel\Converter\Reference\PeriodicalReferenceConverter;

final class PeriodicalReferenceConverterTest extends ReferenceConverterTestCase
{
    protected $referenceClass = PeriodicalReference::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PeriodicalReferenceConverter();
    }
}
