<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\LabsExperimentContentHeaderConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel\ContentHeader;
use Symfony\Component\Asset\Packages;

final class LabsExperimentContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-experiment'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsExperimentContentHeaderConverter(new ContentHeaderImageFactory(new Packages()));
    }
}
