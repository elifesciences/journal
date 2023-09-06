<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;

final class EventSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models =  ['event'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'event'];
    
    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
