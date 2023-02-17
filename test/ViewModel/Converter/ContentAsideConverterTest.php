<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ContentAsideConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\ViewModel;

final class ContentAsideConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ViewModel\ContentAside::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ContentAsideConverter(
            $this->createMock(ViewModelConverter::class),
            $this->stubUrlGenerator()
        );
    }
}
