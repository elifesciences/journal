<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\ViewModel\Converter\EventContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;

final class EventContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['event'];
    protected $class = Event::class;
    protected $viewModelClass = ContentHeaderNonArticle::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new EventContentHeaderConverter();
    }
}
