<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\ApiSdk\Model\Highlight;
use eLife\ApiSdk\Model\Model;
use eLife\Journal\ViewModel\Converter\HighlightCollectionSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use Traversable;

final class HighlightCollectionSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['highlight'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new HighlightCollectionSecondaryTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }

    /**
     * @param Highlight $model
     */
    protected function modelHook(Model $model) : Traversable
    {
        if ($model->getItem() instanceof Collection) {
            yield $model;
        }
    }
}
