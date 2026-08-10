<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleMetaConverter;
use eLife\Patterns\ViewModel\ArticleMeta;
use PHPUnit\Framework\Attributes\Before;

final class ArticleMetaConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [ArticleMeta::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ArticleMetaConverter($this->stubUrlGenerator());
    }
}
