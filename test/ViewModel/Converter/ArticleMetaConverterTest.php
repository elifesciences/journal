<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleMetaConverter;
use eLife\Patterns\ViewModel\ArticleMeta;

final class ArticleMetaConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ArticleMeta::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleMetaConverter($this->stubUrlGenerator());
    }
}
