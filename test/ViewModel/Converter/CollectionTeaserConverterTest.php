<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\ViewModel\Converter\CollectionTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class CollectionTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $class = Collection::class;
    protected $viewModelClass = Teaser::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CollectionTeaserConverter($this->stubUrlGenerator(), $this->stubSlugify());
    }
}
