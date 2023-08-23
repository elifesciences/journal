<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\JobAdvertContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNew;

final class JobAdvertContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['job-advert'];
    protected $viewModelClasses = [ContentHeaderNew::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new JobAdvertContentHeaderConverter($this->stubUrlGenerator());
    }
}
