<?php

namespace test\eLife\Journal\ViewModel\Converter;

use eLife\Journal\ViewModel\Converter\PressPackageTeaserConverter;
use eLife\Patterns\ViewModel\Teaser;

final class PressPackageTeaserConverterTest extends ModelConverterTestCase
{
    protected $models = ['press-package'];
    protected $viewModelClasses = [Teaser::class];

    /**
     * @before
     */
    public function setUpConverter()
    {
        $this->converter = new PressPackageTeaserConverter($this->stubUrlGenerator());
    }
}
