<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\LabsExperiment;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\HighlightLabsExperimentSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Traversable;

final class HighlightLabsExperimentSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['highlight'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new HighlightLabsExperimentSecondaryTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }

    /**
     * @param Highlight $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof LabsExperiment) {
            yield $model;
        }
    }
}
