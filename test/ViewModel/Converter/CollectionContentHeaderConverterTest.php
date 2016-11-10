<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\ViewModel\Converter\CollectionContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;

final class CollectionContentHeaderConverterTest extends ModelConverterTestCase
{
    // multiple models
    protected $models = ['collection'];
    protected $class = Collection::class;
    protected $viewModelClass = ContentHeaderNonArticle::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CollectionContentHeaderConverter($this->stubUrlGenerator());
    }
}
