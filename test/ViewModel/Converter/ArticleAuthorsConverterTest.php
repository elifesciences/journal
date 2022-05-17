<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleAuthorsConverter;
use eLife\Patterns\ViewModel\Authors;

final class ArticleAuthorsConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [Authors::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleAuthorsConverter(
            $this->stubUrlGenerator()
        );
    }
}
