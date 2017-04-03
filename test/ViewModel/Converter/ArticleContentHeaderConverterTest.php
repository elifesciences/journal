<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderArticle;

final class ArticleContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ContentHeaderArticle::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleContentHeaderConverter($this->stubUrlGenerator());
    }
}
