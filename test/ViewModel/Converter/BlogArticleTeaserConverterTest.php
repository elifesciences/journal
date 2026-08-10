<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\BlogArticleTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use PHPUnit\Framework\Attributes\Before;

final class BlogArticleTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $viewModelClasses = [Teaser::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new BlogArticleTeaserConverter($this->stubUrlGenerator());
    }
}
