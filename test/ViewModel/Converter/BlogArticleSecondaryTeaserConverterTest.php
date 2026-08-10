<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\BlogArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use PHPUnit\Framework\Attributes\Before;

final class BlogArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new BlogArticleSecondaryTeaserConverter($this->stubUrlGenerator());
    }
}
