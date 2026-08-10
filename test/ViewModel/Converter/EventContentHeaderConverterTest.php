<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\ViewModel\Converter\EventContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNew;
use PHPUnit\Framework\Attributes\Before;

final class EventContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['event'];
    protected $viewModelClasses = [ContentHeaderNew::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new EventContentHeaderConverter($this->stubUrlGenerator());
    }
}
