<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\MediumArticleSecondaryTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class MediumArticleSecondaryTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['medium-article'];
    protected $viewModelClasses = [Teaser::class];
    protected $context = ['variant' => 'secondary'];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new MediumArticleSecondaryTeaserConverter();
    }
}
