<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ExternalArticleRelatedItemTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;
use PHPUnit\Framework\Attributes\Before;

final class ExternalArticleRelatedItemTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['external-article'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['from' => 'research-article', 'variant' => 'relatedItem'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ExternalArticleRelatedItemTeaserConverter();
    }
}
