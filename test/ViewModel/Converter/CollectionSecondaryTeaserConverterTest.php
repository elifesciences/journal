<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\ViewModel\Converter\CollectionSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class CollectionSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $class = Collection::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CollectionSecondaryTeaserConverter($this->stubUrlGenerator());
    }
}
