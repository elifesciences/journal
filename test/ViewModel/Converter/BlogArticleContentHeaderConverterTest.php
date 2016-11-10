<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Journal\ViewModel\Converter\BlogArticleContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNonArticle;

final class BlogArticleContentHeaderConverterTest extends ModelConverterTestCase
{
    // multiple models
    protected $models = ['blog-article'];
    protected $class = BlogArticle::class;
    protected $viewModelClass = ContentHeaderNonArticle::class;

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BlogArticleContentHeaderConverter($this->stubUrlGenerator());
    }
}
