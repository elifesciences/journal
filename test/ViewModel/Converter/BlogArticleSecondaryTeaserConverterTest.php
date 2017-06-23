<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\BlogArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class BlogArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BlogArticleSecondaryTeaserConverter($this->stubUrlGenerator());
    }
}
