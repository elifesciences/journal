<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Event;
use eLife\Journal\ViewModel\Converter\EventSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class EventSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['event'];
    protected $class = Event::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new EventSecondaryTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }
}
