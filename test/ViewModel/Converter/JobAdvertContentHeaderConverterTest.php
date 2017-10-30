<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\JobAdvertContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeader;

final class JobAdvertContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['job-advert'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new JobAdvertContentHeaderConverter($this->stubUrlGenerator());
    }
}
