<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class ArticleTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleTeaserConverter($this->stubUrlGenerator());
    }
}
