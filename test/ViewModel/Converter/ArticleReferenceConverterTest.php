<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ArticleReferenceConverter;
use eLife\Patterns\ViewModel\Reference;
use PHPUnit\Framework\Attributes\Before;

final class ArticleReferenceConverterTest extends ModelConverterTestCase
{
    protected $models = ['article-poa', 'article-vor'];
    protected $viewModelClasses = [Reference::class];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ArticleReferenceConverter();
    }
}
