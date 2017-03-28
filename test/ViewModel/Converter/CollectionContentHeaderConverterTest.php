<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\ViewModel\Converter\CollectionContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;

final class CollectionContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [ContentHeaderNonArticle::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CollectionContentHeaderConverter($this->stubUrlGenerator());
    }
}
