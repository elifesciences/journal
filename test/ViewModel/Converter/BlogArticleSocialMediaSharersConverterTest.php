<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\BlogArticleSocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;

final class BlogArticleSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new BlogArticleSocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
