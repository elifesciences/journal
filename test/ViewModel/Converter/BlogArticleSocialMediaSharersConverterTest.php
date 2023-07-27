<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;

final class BlogArticleSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'inside-elife-article'];
    
    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
