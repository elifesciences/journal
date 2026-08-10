<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;
use PHPUnit\Framework\Attributes\Before;

final class DigestSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['digest'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['variant' => 'digest'];
    
    #[Before]
    public function setUpConverter(): void
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
