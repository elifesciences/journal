<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ExternalArticleRelatedItemTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class ExternalArticleRelatedItemTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['external-article'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['from' => 'research-article', 'variant' => 'relatedItem'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ExternalArticleRelatedItemTeaserConverter();
    }
}
