<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;
use PHPUnit\Framework\Attributes\Before;

final class BlogArticleSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'inside-elife-article'];
    
    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
