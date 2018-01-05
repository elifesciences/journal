<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\JobAdvertTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class JobAdvertTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['job-advert'];
    protected $viewModelClasses = [Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new JobAdvertTeaserConverter($this->stubUrlGenerator());
    }
}
