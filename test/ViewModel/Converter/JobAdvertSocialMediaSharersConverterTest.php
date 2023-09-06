<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;

final class JobAdvertSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['job-advert'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'job-advert'];
    
    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
