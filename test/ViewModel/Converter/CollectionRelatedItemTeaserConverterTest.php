<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\CollectionRelatedItemTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class CollectionRelatedItemTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'relatedItem', 'from' => 'insight'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CollectionRelatedItemTeaserConverter($this->stubUrlGenerator());
    }
}
