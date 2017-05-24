<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\Collection;
use eLife\Journal\ViewModel\Converter\CollectionContentHeaderConverter;
use eLife\Journal\ViewModel\Factory\ContentHeaderImageFactory;
use eLife\Patterns\ViewModel\ContentHeader;
use Symfony\Component\Asset\Packages;

final class CollectionContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['collection'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new CollectionContentHeaderConverter($this->stubUrlGenerator(), new ContentHeaderImageFactory(new Packages()));
    }
}
