<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\ApiSdk\Model\BlogArticle;
use eLife\Journal\ViewModel\Converter\BlogArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class BlogArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $class = BlogArticle::class;
    protected $viewModelClass = Teaser::class;
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BlogArticleSecondaryTeaserConverter($this->stubUrlGenerator());
    }
}
