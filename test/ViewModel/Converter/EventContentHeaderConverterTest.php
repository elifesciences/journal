<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\ViewModel\Converter\EventContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNew;

final class EventContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['event'];
    protected $viewModelClasses = [ContentHeaderNew::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new EventContentHeaderConverter($this->stubUrlGenerator());
    }
}
