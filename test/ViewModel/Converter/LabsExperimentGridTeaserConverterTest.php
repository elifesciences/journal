<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\LabsExperiment;
use eLife\Journal\ViewModel\Converter\LabsExperimentGridTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class GridTeaserConverterTest extends ModelConverterTestCase
{
    # multiple models
    protected $models = ['labs-experiment'];
    protected $class = LabsExperiment::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'grid'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->converter = new LabsExperimentGridTeaserConverter($this->urlGenerator);
    }
}
