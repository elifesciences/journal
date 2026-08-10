<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;
use PHPUnit\Framework\Attributes\Before;

final class InterviewSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['interview'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'interview'];
    
    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
