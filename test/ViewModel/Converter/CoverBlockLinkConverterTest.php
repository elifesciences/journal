<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\CoverBlockLinkConverter;
use eLife\Journal\ViewModel\Factory\PictureBuilderFactory;
use eLife\Patterns\ViewModel;

final class CoverBlockLinkConverterTest extends ModelConverterTestCase
{
    protected $models = ['cover'];
    protected $viewModelClasses = [ViewModel\BlockLink::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->context['link'] = new ViewModel\Link('link');

        $this->converter = new CoverBlockLinkConverter(new PictureBuilderFactory());
    }
}
