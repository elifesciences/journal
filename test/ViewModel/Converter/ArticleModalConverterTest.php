<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleModalConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel;

final class ArticleModalConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ViewModel\Reference::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleModalConverter(
            $this->createMock(ViewModelConverter::class),
            $this->createMock(PatternRenderer::class),
            $this->stubUrlGenerator()
        );
    }
}
