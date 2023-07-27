<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;

final class SocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['inside-elife-article', 'press-pack', 'digest'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
