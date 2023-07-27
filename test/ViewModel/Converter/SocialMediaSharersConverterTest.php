<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\SocialMediaSharersConverter;
use eLife\Patterns\ViewModel\SocialMediaSharersNew;

final class SocialMediaSharersConverterTest extends ModelConverterTestCase
{
    protected $models = ['blog-article', 'press-package', 'digest'];
    protected $viewModelClasses = [SocialMediaSharersNew::class];
    protected $context = ['pageType' => 'press-pack'];
    
    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new SocialMediaSharersConverter($this->stubUrlGenerator());
    }
}
