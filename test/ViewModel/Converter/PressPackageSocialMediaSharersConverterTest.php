<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PressPackageSocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;

final class PressPackageSocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['press-package'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PressPackageSocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
