<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\ViewModel\Converter\EventTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use PHPUnit\Framework\Attributes\Before;

final class EventTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['event'];
    protected $viewModelClasses = [Teaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new EventTeaserConverter($this->stubUrlGenerator());
    }
}
