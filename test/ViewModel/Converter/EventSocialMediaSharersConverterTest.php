<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;
use PHPUnit\Framework\Attributes\Before;

final class EventSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models =  ['event'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'event'];
    
    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
