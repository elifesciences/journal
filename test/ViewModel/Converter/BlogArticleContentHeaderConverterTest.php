<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\BlogArticleContentHeaderConverter;
use eLife\Patterns\ViewModel\ContentHeaderNew;
use PHPUnit\Framework\Attributes\Before;

final class BlogArticleContentHeaderConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $viewModelClasses = [ContentHeaderNew::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new BlogArticleContentHeaderConverter($this->stubUrlGenerator());
    }
}
