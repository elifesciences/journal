<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ExternalArticleReadMoreItemConverter;
use eLife\Patterns\ViewModel\ReadMoreItem;
use PHPUnit\Framework\Attributes\Before;

final class ExternalArticleReadMoreItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['external-article'];
    protected $viewModelClasses = [ReadMoreItem::class];
    protected $context = ['from' => 'research-article', 'variant' => 'relatedItem'];

    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new ExternalArticleReadMoreItemConverter();
    }
}
