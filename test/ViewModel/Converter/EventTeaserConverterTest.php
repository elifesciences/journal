<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\ViewModel\Converter\EventTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class EventTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['event'];
    protected $class = Event::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new EventTeaserConverter($this->stubUrlGenerator());
    }
}
