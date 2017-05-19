<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ExternalArticleReadMoreItemConverter;
use eLife\Journal\ViewModel\Converter\ViewModelConverter;
use eLife\Journal\ViewModel\Paragraph;
use eLife\Patterns\PatternRenderer;
use eLife\Patterns\ViewModel\ReadMoreItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
