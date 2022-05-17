<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleReferenceConverter;
use eLife\Patterns\ViewModel\Reference;

final class ArticleReferenceConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [Reference::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleReferenceConverter();
    }
}
