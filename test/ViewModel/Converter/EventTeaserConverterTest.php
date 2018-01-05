<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\ViewModel\Converter\EventTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class EventTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['event'];
    protected $viewModelClasses = [Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new EventTeaserConverter($this->stubUrlGenerator());
    }
}
