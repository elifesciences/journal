<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;
use PHPUnit\Framework\Attributes\Before;

final class JobAdvertSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['job-advert'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'job-advert'];
    
    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
