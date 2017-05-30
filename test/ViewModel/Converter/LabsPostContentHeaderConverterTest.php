<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsPostContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeader;

final class LabsPostContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-post'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsPostContentHeaderConverter($this->stubUrlGenerator());
    }
}
