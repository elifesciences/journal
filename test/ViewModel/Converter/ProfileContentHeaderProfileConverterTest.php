<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\ProfileContentHeaderProfileConverter;
use eLife\Patterns\ViewModel\ContentHeaderProfile;

final class ProfileContentHeaderProfileConverterTest extends ModelConverterTestCase
{
    protected $models = ['profile'];
    protected $viewModelClasses = [ContentHeaderProfile::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new ProfileContentHeaderProfileConverter($this->stubUrlGenerator());
    }
}
