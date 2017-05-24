<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsExperimentContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeader;

final class LabsExperimentContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-experiment'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsExperimentContentHeaderConverter();
    }
}
