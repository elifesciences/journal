<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Journal\ViewModel\Converter\LabsExperimentSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class LabsExperimentSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    // multiple models
    protected $models = ['labs-experiment'];
    protected $class = LabsExperiment::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsExperimentSecondaryTeaserConverter($this->stubUrlGenerator());
    }
}
