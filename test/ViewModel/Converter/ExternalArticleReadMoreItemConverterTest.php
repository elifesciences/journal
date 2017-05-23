<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ExternalArticleReadMoreItemConverter;
use eLife\Patterns\ViewModel\ReadMoreItem;

final class ExternalArticleReadMoreItemConverterTest extends ModelConverterTestCase
{
    protected $models = ['external-article'];
    protected $viewModelClasses = [ReadMoreItem::class];
    protected $context = ['from' => 'research-article', 'variant' => 'relatedItem'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ExternalArticleReadMoreItemConverter();
    }
}
