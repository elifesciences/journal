<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Interview;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\HighlightInterviewSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Traversable;

final class HighlightInterviewSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['highlight'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new HighlightInterviewSecondaryTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }

    /**
     * @param Highlight $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof Interview) {
            yield $model;
        }
    }
}
