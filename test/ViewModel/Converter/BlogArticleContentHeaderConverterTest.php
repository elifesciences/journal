<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\BlogArticleContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeader;

final class BlogArticleContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $viewModelClasses = [ContentHeader::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BlogArticleContentHeaderConverter($this->stubUrlGenerator());
    }
}
