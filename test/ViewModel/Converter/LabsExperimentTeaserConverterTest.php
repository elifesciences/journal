<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Journal\ViewModel\Converter\LabsExperimentTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class LabsExperimentTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['labs-experiment'];
    protected $class = LabsExperiment::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new LabsExperimentTeaserConverter($this->stubUrlGenerator(), $this->stubTranslator());
    }
}
