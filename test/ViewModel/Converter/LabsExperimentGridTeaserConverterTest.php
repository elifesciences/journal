<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Journal\ViewModel\Converter\LabsExperimentGridTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class LabsExperimentGridTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-experiment'];
    protected $class = LabsExperiment::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'grid'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsExperimentGridTeaserConverter($this->stubUrlGenerator());
    }
}
