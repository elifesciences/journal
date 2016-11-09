<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\ViewModel\Converter\EventContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EventContentHeaderConverterTest extends ModelConverterTestCase
{
    # multiple models
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
