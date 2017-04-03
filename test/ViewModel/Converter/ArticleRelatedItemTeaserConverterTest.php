<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleRelatedItemTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class ArticleRelatedItemTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'relatedItem', 'from' => 'insight'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ArticleRelatedItemTeaserConverter($this->stubUrlGenerator());
    }
}
