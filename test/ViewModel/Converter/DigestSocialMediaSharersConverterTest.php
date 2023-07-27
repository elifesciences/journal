<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;

final class DigestSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['digest'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'digest'];
    
    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
